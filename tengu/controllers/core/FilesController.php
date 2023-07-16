<?php

/**
 * Form controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\FilesController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright 2022 Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Files as Model;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;

class FilesController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;

    public $access = [];
    public $global_url = '/files';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Model())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->softDelete();

            $this->saveFormDeleted('File has been deleted');

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->redirect(UrlHelper::backend($url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function downloadAction()
    {
        $model = (new Model())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => self::decrypt(urldecode($this->dispatcher->getParam('id')))
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        if (!in_array($model->resource, ['image'])) {
            $this->secure();
        }

        try {
            $this->addLog(
                $model->resource,
                $model->id,
                'info',
                'Downloaded by ' . $this->getUserFullName()
            );

            $this->downloadFile($model);
        } catch (Exception $err) {
            throw new RequestException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function outputAction()
    {
        $model = (new Model())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => self::decrypt(urldecode($this->dispatcher->getParam('id')))
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        $this->outputFile($model);
        die();
    }
}
