<?php

/**
 * Boards controller.
 *
 * @package     Kytschi\Umi\Controllers\BoardsController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Umi\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Umi\Models\Boards;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class BoardsController extends ControllerBase
{
    use Logs;

    public $access = [
        'administrator',
        'super-user',
        'project-manager'
    ];

    public $global_url = '/boards';
    public $resource = 'board';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->pms . $this->global_url;
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Boards())->findFirst([
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

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Board has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Boards())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->recover();

            $this->addLog(
                $this->resource,
                $model->id,
                'warning',
                'Recovered by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Board has been recovered');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }
}
