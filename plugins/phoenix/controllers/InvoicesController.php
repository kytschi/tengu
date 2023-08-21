<?php

/**
 * Invoices controller.
 *
 * @package     Kytschi\Wako\Controllers\InvoicesController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
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

namespace Kytschi\Wako\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Mai\Models\Projects;
use Kytschi\Mai\Models\Timesheets;
use Kytschi\Wako\Models\Invoices;
use Kytschi\Wako\Models\InvoiceTimesheets;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Encryption\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class InvoicesController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Notes;
    use Pagination;
    use Tags;

    public $access = [
        'administrator',
        'super-user',
        'finance-manager'
    ];

    public $global_url = '/invoices';
    public $resource = 'invoice';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->fms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Adding a invoice');

        return $this->view->partial(
            'wako/invoices/add'
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Invoices())->findFirst([
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

            $this->saveFormDeleted('Invoice has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (\Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function editAction()
    {
        $this->secure($this->access);

        $model = (new Invoices())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle($model->name);

        return $this->view->partial(
            'wako/invoices/edit',
            [
                'data' => $model,
                'projects' => Projects::find(['conditions' => 'deleted_at IS NULL']),
                'timesheets' => Timesheets::find([
                    'conditions' => 'deleted_at IS NULL',
                    'binds' => [
                        'order' => 'name'
                    ]
                ])
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Our invoices');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'name',
                'created_by',
                'status'
            ],
            'name'
        );


        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Invoices::class)
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'name' => '%' . $this->search . '%',
                'search_tags' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('name LIKE :name: OR search_tags LIKE :search_tags:');
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
            'wako/invoices/index',
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

        $model = (new Invoices())->findFirst([
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

            $this->saveFormUpdated('Invoice has been recovered');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (\Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function saveAction()
    {
        $this->secure($this->access);

        try {
            $this->validate();

            $model = $this->setData(new Invoices());

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to add the invoice',
                    $model->getMessages()
                );
            }

            $this->addTags($this->resource, $model->id, self::getUserId(), true);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved('Invoice has been saved');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/add'));
        } catch (Exception $err) {
            if (!empty($model)) {
                $model->delete();
            }

            if (!empty($board)) {
                $board->delete();
            }

            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function setData($model)
    {
        $model->name = $_POST['name'];
        $model->amount = floatval($_POST['amount']);
        $model->status = !empty($_POST['status']) ? $this->isValidStatus($_POST['status']) : $this->default_status;
        $model->timesheet_amount = isset($_POST['timesheet_amount']) ? true : false;
        $model->project_id = !empty($_POST['project_id']) ? $_POST['project_id'] : null;

        $model = $this->addSearchTags($model);

        return $model;
    }

    public function stats()
    {
        $model = new Invoices();
        $table = $model->getSource();

        $query = 'SELECT ';
        $query .= "(
            SELECT
                SUM(amount)
            FROM 
                $table
            WHERE 
                status = 'active' AND
                created_at BETWEEN '" . date('Y') . '-01-01' . "' AND '" . date('Y') . '-12-31' . "'
        ) AS incoming_annual,";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Invoices())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $this->validate();

            $model = $this->setData($model);

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the invoice',
                    $model->getMessages()
                );
            }

            if ($model->timesheet_amount) {
                $amount = 0;

                foreach ($model->timesheets as $timesheet) {
                    $amount += $timesheet->amount;
                }

                $model->amount = $amount;
                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the invoice',
                        $model->getMessages()
                    );
                }
            }

            $this->addTags($this->resource, $model->id, self::getUserId(), true);
            $this->addNote($this->resource, $model->id);

            if (!empty($_POST['timesheet_id'])) {
                $this
                    ->modelsManager
                    ->executeQuery(
                        'UPDATE ' .
                        InvoiceTimesheets::class .
                        ' SET deleted_at=NOW(), deleted_by=:deleted_by: WHERE invoice_id = :invoice_id:',
                        [
                            'deleted_by' => self::getUserId(),
                            'invoice_id' => $model->id
                        ]
                    );
                
                $table = (new InvoiceTimesheets())->getSource();
                $query = '';

                $params = [
                    ':invoice_id' => $model->id,
                    ':created_at' => date('Y-m-d H:i:s'),
                    ':created_by' => self::getUserId(),
                    ':updated_at' => date('Y-m-d H:i:s'),
                    ':updated_by' => self::getUserId()
                ];

                foreach ($_POST['timesheet_ids'] as $key => $id) {
                    if (empty($id)) {
                        continue;
                    }
                    
                    $query .=
                        'INSERT INTO ' . $table . '
                        (id, invoice_id, timesheet_id, created_at, created_by, updated_at, updated_by)
                            SELECT
                                :id_' . $key . ',
                                :invoice_id,
                                :timesheet_id_' . $key . ',
                                :created_at,
                                :created_by,
                                :updated_at,
                                :updated_by
                            FROM DUAL
                            WHERE NOT EXISTS
                            (
                                SELECT
                                    id,
                                    invoice_id,
                                    timesheet_id,
                                    created_at,
                                    created_by,
                                    updated_at,
                                    updated_by
                                FROM ' . $table . '
                                WHERE
                                    invoice_id=:invoice_id AND timesheet_id=:timesheet_id_' . $key . '
                            );
                        UPDATE ' . $table . ' SET deleted_at=NULL, deleted_by=NULL
                        WHERE invoice_id=:invoice_id AND timesheet_id=:timesheet_id_' . $key . ';';

                    $params = array_merge(
                        $params,
                        [
                            ':id_' . $key => (new Random())->uuid(),
                            ':timesheet_id_' . $key => $id
                        ]
                    );
                }

                if ($query) {
                    $this->db->query($query, $params);
                }
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Invoice has been updated');

            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function validate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            'name',
            new PresenceOf(
                [
                    'message' => 'The name is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
