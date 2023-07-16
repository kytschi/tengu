<?php

/**
 * Files traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Files
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright 2022 Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Traits\Core;

use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Models\Core\Files as Model;
use Kytschi\Tengu\Models\Core\FileDownloadHistory;
use Kytschi\Tengu\Models\Website\PageFiles;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Traits\Core\Json;
use Kytschi\Tengu\Traits\Core\Security;
use Kytschi\Tengu\Traits\Core\User;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

trait Files
{
    use Json;
    use Security;
    use User;
    
    public function addCarouselImages($resource_id, $user_id)
    {
        $this
            ->modelsManager
            ->executeQuery(
                'UPDATE ' .
                PageFiles::class .
                ' SET deleted_at=NOW(), deleted_by=:deleted_by: WHERE page_id = :page_id: AND resource="carousel"',
                [
                    'deleted_by' => $user_id,
                    'page_id' => $resource_id
                ]
            );

        if (empty($_POST['carousel_image'])) {
            return;
        }

        foreach ($_POST['carousel_image'] as $file_id) {
            $this->db->query(
                'INSERT INTO
                    page_files (id, page_id, file_id, resource, created_at, created_by, updated_at, updated_by)
                    SELECT
                        :id,
                        :page_id,
                        :file_id,
                        :resource,
                        :created_at,
                        :created_by,
                        :updated_at,
                        :updated_by
                    FROM DUAL
                    WHERE NOT EXISTS
                    (
                        SELECT
                            id,
                            page_id,
                            file_id,
                            resource,
                            created_at,
                            created_by,
                            updated_at,
                            updated_by
                        FROM page_files
                        WHERE
                            page_id=:page_id AND file_id=:file_id
                    );
                UPDATE page_files SET deleted_at=NULL, deleted_by=NULL WHERE page_id=:page_id AND file_id=:file_id',
                [
                    ':id' => (new Random())->uuid(),
                    ':page_id' => $resource_id,
                    ':file_id' => $file_id,
                    ':resource' => 'carousel',
                    ':created_at' => date('Y-m-d H:i:s'),
                    ':created_by' => $user_id,
                    ':updated_at' => date('Y-m-d H:i:s'),
                    ':updated_by' => $user_id
                ]
            );
        }
    }

    public function addFile(
        string $resource_id,
        $file,
        string $type = '',
        string $label = '',
        string $filename = '',
        bool $copy = true,
        bool $compress = false,
        bool $thumb = true,
        string $dir = ''
    ) {
        if (empty($type)) {
            $type = $this->resource;
        }

        if (is_string($file)) {
            $splits = explode("\\", $file);
            $name = end($splits);
            $tmp_name = $name;
            $mime_type = mime_content_type($file);
        } else {
            $name = $file['name'];
            $tmp_name = $file['tmp_name'];
            $mime_type = $file['type'];
        }

        if (empty($label)) {
            $label = str_replace(
                ['-', '/', '\\'],
                ' ',
                explode('.', $name)[0]
            );
        }

        if (empty($dir)) {
            $dir = ($this->di->getConfig())->application->dumpDir;
        }

        $file_model = new Model(
            [
                'resource_id' => $resource_id,
                'name' => $name,
                'resource' => $type,
                'mime_type' => $mime_type,
                'label' => $label,
                'compress' => $compress
            ]
        );

        if ($file_model->save() === false) {
            throw new SaveException(
                'Failed to create the file entry',
                $file_model->getMessages()
            );
        }

        if ($filename) {
            $file_model->filename = $filename;
        } else {
            $file_model->filename =
                $file_model->id .
                '-' .
                strtolower(str_replace([' ', '/', '\\'], '-', $file_model->name));
        }

        if ($file_model->update() === false) {
            throw new SaveException(
                'Failed to update the file entry',
                $file_model->getMessages()
            );
        }

        if ($copy) {
            if (!$compress) {
                copy(
                    $tmp_name,
                    $dir . $file_model->filename
                );

                switch ($mime_type) {
                    case 'image/jpeg':
                        shell_exec('jpegoptim -m 60 ' . $dir . $file_model->filename);
                        break;
                    case 'image/png':
                        shell_exec('optipng ' . $dir . $file_model->filename);
                        break;
                }

                if ($thumb) {
                    $this->createThumb($file_model, $file, $dir);
                }
            }
        }

        if ($compress) {
            file_put_contents(
                $dir . $file_model->filename,
                self::encrypt(file_get_contents($tmp_name), $this->tengu->settings->compress_key)
            );
        }

        return $file_model;
    }

    public function addFiles($resource, $resource_id)
    {
        if (!empty($_FILES['file'])) {
            if (
                empty($_FILES['file']['name']) ||
                empty($_FILES['file']['tmp_name']) ||
                empty($_FILES['file']['error']) ||
                empty($_FILES['file']['size'])
            ) {
                return;
            }
            
            if (!is_array($_FILES['file']['name'])) {
                $this->addFile(
                    $resource_id,
                    $_FILES['file'],
                    $resource,
                    !empty($_POST['file_label']) ?
                        (empty($_POST['file_label'][0]) ? $_POST['file_label'][0] : '')
                        : ''
                );

                return;
            }

            foreach ($_FILES['file']['name'] as $key => $file) {
                $this->addFile(
                    $resource_id,
                    [
                        'name' => $file,
                        'type' => $_FILES['file']['type'][$key],
                        'tmp_name' => $_FILES['file']['tmp_name'][$key],
                        'error' => $_FILES['file']['error'][$key],
                        'size' => $_FILES['file']['size'][$key]
                    ],
                    $resource,
                    !empty($_POST['file_label']) ?
                        (empty($_POST['file_label'][$key]) ? $_POST['file_label'][$key] : '')
                        : ''
                );
            }
        }
    }

    public function addImage($resource_id)
    {
        $file = null;
        
        if (!empty($_FILES['image']['name'])) {
            $label = $_FILES['image']['name'];
            if (!empty($_POST['image_label'])) {
                $label = $_POST['image_label'];
            }
            
            $file = $this->addFile(
                $resource_id,
                $_FILES['image'],
                'image',
                $label
            );

            $this->createThumb($file, $_FILES['image']);
        }

        return $file;
    }

    public function addAjaxImage()
    {
        try {
            if (empty($_POST)) {
                throw new RequestException('Invalid post data');
            }

            $validation = new Validation();
            $validation->add(
                'resource_id',
                new PresenceOf(
                    [
                        'message' => 'The resource Id is required',
                    ]
                )
            );

            $messages = $validation->validate($_POST);
            if (count($messages)) {
                throw new ValidationException('Form validation failed, please double check the required fields', $messages);
            }

            $obj = new \stdClass();
            $obj->message = 'Image uploaded';

            $file = $this->addImage($_POST['resource_id']);
            $obj->data = (object) $file->toArray();
            $obj->data->url = $file->url;
            $obj->data->thumb_url = $file->thumb_url;
            $this->outputJson($obj);
        } catch (Exception $err) {
            $this->jsonError($err);
        }
    }

    public function clearFiles($resource, $resource_id)
    {
        $this
            ->modelsManager
            ->executeQuery(
                'UPDATE ' .
                Model::class .
                ' SET deleted_at=NOW(), deleted_by=:deleted_by: 
                WHERE resource = :resource: AND resource_id = :resource_id:',
                [
                    'deleted_by' => self::getUserId(),
                    'resource' => $resource,
                    'resource_id' => $resource_id
                ]
            );
    }

    public function createThumb($model, $file, $dir = '')
    {
        if (empty($dir)) {
            $dir = ($this->di->getConfig())->application->dumpDir;
        }

        if (!file_exists($file['tmp_name'])) {
            return;
        }

        $filename = 'thumb-' . $model->filename;
        list($width, $height) = getimagesize($file['tmp_name']);
        $desired_width = 400;
        
        switch ($file['type']) {
            case 'image/jpeg':
                $upload = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $upload = imagecreatefrompng($file['tmp_name']);
                break;
        }

        $width = imagesx($upload);
        $height = imagesy($upload);

        $desired_height = floor($height * ($desired_width / $width));
        $save = imagecreatetruecolor($desired_width, $desired_height);
        imagecopyresampled($save, $upload, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

        switch ($file['type']) {
            case 'image/jpeg':
                imagejpeg($save, $dir . $filename, 60);
                shell_exec('jpegoptim -m 60 ' . $save);
                break;
            case 'image/png':
                imagepng($save, $dir . $filename, 6);
                shell_exec('optipng ' . $save);
                break;
        }
    }

    public function downloadFile($model)
    {
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $model->filename);
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        
        $this->outputFile($model);

        (new FileDownloadHistory([
            'file_id' => $model->id,
            'summary' => 'Downloaded by ' . $this->getUserFullName()
        ]))->save();
        die();
    }

    public function getImages()
    {
        return (new Model())->find([
            'conditions' => '
                deleted_at IS NULL AND
                mime_type IN ("image/png", "image/jpeg", "image/jpg") AND
                resource NOT IN ("profile-image")',
            'order' => 'created_at DESC'
        ]);
    }

    public function createTempFile($model)
    {
        $file = ($this->di->getConfig())->application->tmpDir . $model->filename;
        file_put_contents(
            $file,
            $this->readFile($model)
        );

        return $file;
    }

    public function deleteTempFile($file)
    {
        unlink($file);
    }

    public function readFile($model)
    {
        if ($model->compress) {
            return self::decrypt(file_get_contents($model->location), $this->tengu->settings->compress_key);
        } else {
            return file_get_contents($model->location);
        }
    }

    public function outputFile($model)
    {
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Type: ' . $model->mime_type);

        echo $this->readFile($model);
    }

    public function validFile($file)
    {
        if (!in_array($file['type'], $this->valid_files)) {
            throw new ValidationException('That file type is not supported');
        }
    }
}
