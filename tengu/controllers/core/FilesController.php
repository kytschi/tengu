<?php

/**
 * Files controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\FilesController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301, USA.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Api;
use Kytschi\Tengu\Models\Core\Files as Model;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;

class FilesController extends ControllerBase
{
    use Api;
    use Files;
    use Form;
    use Logs;

    public $access = [];
    public $global_url = '/files';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    public function apiImagesAction(string $page, string $limit)
    {
        $this->apiSecure();
        $this->apiResponse('images', $this->getImages($page, $limit));
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
