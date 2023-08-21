<?php

/**
 * Logs controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\LogsController
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
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Pagination;
use Phalcon\Paginator\Adapter\QueryBuilder;

class LogsController extends ControllerBase
{
    use Form;
    use Pagination;

    public $access = [
        'super-user'
    ];

    public $global_url = '/logs';
    public $resource = 'log';

    public function clearAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        try {
            $this
                ->modelsManager
                ->executeQuery('DELETE FROM ' . Logs::class . ' WHERE id IS NOT NULL');

            $this->saveFormDeleted('Logs have been cleared');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Logs())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $this
                ->modelsManager
                ->executeQuery('DELETE FROM ' . Logs::class . ' WHERE id = "' . $model->id . '"');

            $this->saveFormDeleted('Log has been deleted');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function editAction()
    {
        $this->secure($this->access);

        $this->setPageTitle('Viewing the log');

        $model = (new Logs())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        return $this->view->partial(
            'core/logs/edit',
            [
                'data' => $model
            ]
        );
    }

    public static function getBySource($source)
    {
        return (new Logs())->find([
            'conditions' => 'deleted_at IS NULL AND source = :source:',
            'params' => [
                'source' => $source
            ],
            'order' => 'created_at DESC'
        ]);
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure([
            'super-user'
        ]);

        $this->setPageTitle('Logs');

        $this->savePagination();

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Logs::class)
            ->where('id != ""')
            ->orderBy('created_at DESC');

        if (!empty($this->search)) {
            $builder
                ->andWhere('summary LIKE :summary:')
                ->setBindParams(
                    [
                        'summary' => '%' . $this->search . '%'
                    ]
                );
        }

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        return $this->view->partial(
            'core/logs/index',
            [
                'data' => $paginator->paginate(),
                'stats' => $this->stats()
            ]
        );
    }

    public function stats()
    {
        $table = (new Logs())->getSource();

        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE type = 'danger') AS danger";

        $model = new Logs();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function viewAction()
    {
        $this->clearFormData();

        $this->secure([
            'super-user'
        ]);

        $model = (new Logs())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        return $this->view->partial(
            'logs/view',
            [
                'data' => $model
            ]
        );
    }
}
