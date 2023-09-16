<?php

/**
 * Invoices controller.
 *
 * @package     Kytschi\Wako\Controllers\InvoicesController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Wako\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Projects;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Api;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Mai\Models\Timesheets;
use Kytschi\Wako\Controllers\SettingsController;
use Kytschi\Wako\Models\Invoices;
use Kytschi\Wako\Models\InvoiceTimesheets;
use Kytschi\Wako\Traits\TaxYear;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Encryption\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class InvoicesController extends ControllerBase
{
    use Api;
    use Files;
    use Form;
    use Logs;
    use Notes;
    use Pagination;
    use Tags;
    use TaxYear;

    public $access = [
        'administrator',
        'super-user',
        'finance-manager'
    ];

    public $global_url = '/invoices';
    public $resource = 'invoice';

    public $directions = [
        'incoming',
        'outgoing'
    ];

    public $statuses = [
        'outstanding',
        'paid',
        'unpaid',
        'deleted',
    ];

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->fms . $this->global_url;
        $this->orderDir = 'desc';
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Adding a invoice');

        return $this->view->partial(
            'wako/invoices/add',
            [
                'directions' => $this->directions,
                'projects' => Projects::find(['order' => 'name']),
            ]
        );
    }

    public function calYears()
    {
        $model = new Invoices();
        $table = $model->getSource();

        $query = "SELECT YEAR(issued_on) as 'year' FROM $table GROUP BY YEAR(issued_on)";
        $params = [];

        $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','), $params)
        ))->toArray();

        $return = [];
        foreach ($results as $result) {
            $return[] = $result['year'];
        }

        return $return;
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

        if ($model->ref) {
            $this->setPageSubTitle('Invoice reference: ' . $model->ref);
        }
        $this->setPageTitle($model->name);
        
        return $this->view->partial(
            'wako/invoices/edit',
            [
                'data' => $model,
                'directions' => $this->directions,
                'projects' => Projects::find(['order' => 'name']),
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
                'issued_on',
                'status'
            ],
            'issued_on'
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

        $tax_year = $this->getCurrentTaxYear();
        $this->setPageSubTitle(
            'Selected tax year&nbsp;<strong>' . $tax_year->code . '&nbsp;' .
            DateHelper::pretty($tax_year->tax_year_start, false) . '</strong>&nbsp;to&nbsp;<strong>' .
            DateHelper::pretty($tax_year->tax_year_end, false) . '</strong>'
        );
                
        if (!empty($this->filters)) {
            $iLoop = 1;
            foreach ($this->filters as $filter => $value) {
                if (empty($value)) {
                    continue;
                }
                switch ($filter) {
                    case 'direction':
                        $builder->andWhere('direction LIKE :direction_' . $iLoop . ':');
                        $params['direction_' . $iLoop] = $value;
                        $iLoop++;
                        break;
                    case 'project':
                        $builder->andWhere('project_id = :project_id:');
                        $params['project_id'] = $value;
                        $iLoop++;
                        break;
                    case 'status':
                        $builder->andWhere('status LIKE :status_' . $iLoop . ':');
                        $params['status_' . $iLoop] = $value;
                        $iLoop++;
                        break;
                    case 'taxable':
                        $builder->andWhere('taxable = :taxable:');
                        $params['taxable'] = ($value == 'yes' ? 1 : 0);
                        $iLoop++;
                        break;
                    case 'year':
                        $builder->andWhere('tax_year_id = :tax_year_id:');
                        $params['tax_year_id'] = $value;
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
                'directions' => $this->directions,
                'projects' => Projects::find(['order' => 'name']),
                'statuses' => $this->statuses,
                'stats' => $this->stats($tax_year->tax_year_start, $tax_year->tax_year_end),
                'tax_year' => $tax_year,
                'years' => $this->getTaxYears()
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

            $this->addTagsFromRequest($model->id, true);

            if (!empty($_FILES['upload_invoice']['name'])) {
                $this->validFile($_FILES['upload_invoice']);
                $this->clearFiles($this->resource, $model->id);
                
                $this->addFile(
                    $model->id,
                    $_FILES['upload_invoice'],
                    $this->resource,
                    $model->name,
                    '',
                    false,
                    true
                );
            }

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

    public function searchAction()
    {
        $this->apiSecure();

        $query = '';
        $params = [];
        $return = [];

        if (!empty($_POST['query'])) {
            $query = 'name LIKE :name: OR search_tags LIKE :search_tags:';
            $params = [
                'name' => '%' . strtolower($_POST['query']) . '%',
                'search_tags' => '%' . strtolower($_POST['query']) . '%'
            ];
        
            if (
                $results = (new Invoices())->find([
                    'conditions' => $query,
                    'bind' => $params,
                    'order' => 'issued_on DESC'
                ])
            ) {
                foreach ($results as $result) {
                    $obj = new \stdClass();
                    $obj->id = $result->id;
                    $obj->name = $result->name . (!empty($result->ref) ? '<br/>Ref: ' . $result->ref : '');
                    $obj->date = $result->issued_on;
                    $obj->url = !empty($result->file) ? $result->file->output_url : '';
                    $return[] = $obj;
                }
            }
        }

        $this->apiResponse(
            'found invoices',
            $return,
            200,
            !empty($_POST['query']) ? $_POST['query'] : ''
        );
    }

    public function setData($model)
    {
        $model->name = $_POST['name'];
        $model->amount = floatval($_POST['amount']);
        $model->direction = $_POST['direction'];
        $model->issued_on = !empty($_POST['issued_on']) ? DateHelper::sql($_POST['issued_on'], false) : null;
        $model->paid_on = !empty($_POST['paid_on']) ? DateHelper::sql($_POST['paid_on'], false) : null;
        $model->timesheet_amount = isset($_POST['timesheet_amount']) ? true : false;
        $model->project_id = !empty($_POST['project_id']) ? $_POST['project_id'] : null;
        $model->ref = !empty($_POST['ref']) ? $_POST['ref'] : null;

        if (!empty($model->issued_on)) {
            $model->tax_year_id = $this->getTaxYearId($model->issued_on);
        }

        if (!empty($model->paid_on)) {
            $model->status = 'paid';
        }

        $model = $this->addSearchTags($model);

        return $model;
    }

    public function stats($start, $end)
    {
        $params = [
            'incoming_start' => $start,
            'incoming_end' => $end,
            'outgoing_start' => $start,
            'outgoing_end' => $end,
            'vat_start' => $start,
            'vat_end' => $end
        ];

        $model = new Invoices();
        $table = $model->getSource();

        $search = '';
        if (!empty($this->search)) {
            $params = array_merge(
                $params,
                [
                    'name' => '%' . $this->search . '%',
                    'search_tags' => '%' . $this->search . '%'
                ]
            );
            $search = ' AND (name LIKE :name OR search_tags LIKE :search_tags)';
        }

        $query = "SELECT
        (
            SELECT
                SUM(amount)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND 
                direction = 'incoming' AND 
                issued_on BETWEEN :incoming_start AND :incoming_end
        ) AS incoming,
        (
            SELECT
                SUM(amount)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND 
                direction = 'outgoing' AND 
                issued_on BETWEEN :outgoing_start AND :outgoing_end
                $search
        ) AS outgoing,
        (
            SELECT
                SUM(vat)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND 
                direction = 'outgoing' AND 
                issued_on BETWEEN :vat_start AND :vat_end
                $search
        ) AS vat,
        (
            SELECT
                count(id)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND 
                paid_on IS NULL
                $search
        ) AS unpaid";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','), $params)
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

            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);

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

            if (!empty($_FILES['upload_invoice']['name'])) {
                $this->validFile($_FILES['upload_invoice']);
                $this->clearFiles($this->resource, $model->id);
                
                $this->addFile(
                    $model->id,
                    $_FILES['upload_invoice'],
                    $this->resource,
                    $model->name,
                    '',
                    false,
                    true
                );
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

        $validation->add(
            'amount',
            new PresenceOf(
                [
                    'message' => 'The amount is required',
                ]
            )
        );

        $validation->add(
            'direction',
            new PresenceOf(
                [
                    'message' => 'The direction is required',
                ]
            )
        );

        $validation->add(
            'issued_on',
            new PresenceOf(
                [
                    'message' => 'The issued on is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
