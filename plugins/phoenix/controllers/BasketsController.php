<?php

/**
 * Baskets controller.
 *
 * @package     Kytschi\Phoenix\Controllers\BasketsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Phoenix\Models\Baskets;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Pagination;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class BasketsController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Pagination;

    public $access = [
        'super-user'
    ];

    public $global_url = '/baskets';
    public $resource = 'basket';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->sales . $this->global_url;
    }

    public function deleteAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        $model = (new Baskets())->findFirst([
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

            $this->saveFormDeleted('Basket successfully marked as deleted');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    public function editAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Editing the template');

        $model = (new Baskets())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        return $this->view->partial(
            'website/templates/edit',
            [
                'data' => $model,
                'statuses' => $this->defaultStatuses()
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Baskets');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'created_by',
                'status'
            ],
            'status'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Baskets::class)
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'name' => '%' . $this->search . '%',
                'url' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('name LIKE :name: OR url LIKE :url:');
        }

        if (!empty($this->filters)) {
            $iLoop = 1;
            foreach ($this->filters as $filter => $value) {
                if (empty($value)) {
                    continue;
                }

                switch ($filter) {
                    case 'status':
                        $builder->andWhere('status LIKE :status_' . $iLoop . ':');
                        $params['status_' . $iLoop] = $value;
                        $iLoop++;
                        break;
                }
            }
        }

        $builder->setBindParams($params);

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        return $this->view->partial(
            'phoenix/baskets/index',
            [
                'data' => $paginator->paginate(),
                'stats' => $this->stats()
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        $model = (new Baskets())->findFirst([
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

            $this->saveFormUpdated('Basket successfully recovered');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    public function resumeAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        $model = (new Baskets())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            if ($model->status == 'active') {
                throw new SaveException(
                    'Failed to resume the basket as it is already being worked on'
                );
            } elseif ($model->status == 'deleted') {
                throw new SaveException(
                    'Failed to resume the basket as it is in a deleted state'
                );
            }

            $model->status = 'active';

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to resume the basket',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Resumed by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Basket successfully resumed');
            $this->redirect(($this->di->getConfig())->urls->sales . '/products');
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    public function stats()
    {
        $model = new Baskets();
        $table = $model->getSource();

        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE status = 'complete') AS complete,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'active') AS active,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'inactive') AS inactive,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'deleted' AND deleted_at IS NOT NULL) AS deleted,";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }
}
