<?php

/**
 * Tax years controller.
 *
 * @package     Kytschi\Wako\Controllers\TaxYearsController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Wako\Controllers;

use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\NumberHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Wako\Controllers\InvoicesController;
use Kytschi\Wako\Controllers\SettingsController;
use Kytschi\Wako\Models\Invoices;
use Kytschi\Wako\Models\Receipts;
use Kytschi\Wako\Models\StatementItems;
use Kytschi\Wako\Models\StatementItemInvoices;
use Kytschi\Wako\Models\StatementItemReceipts;
use Kytschi\Wako\Models\TaxYears;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class TaxYearsController extends InvoicesController
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

    public $global_url = '/tax-years';
    public $resource = 'tax-year';

    public $default_status = 'current';
    public $valid_status = [
        'current',
        'ended'
    ];
    
    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->fms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Adding a tax year');

        return $this->view->partial(
            'wako/tax_years/add'
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        $model = (new TaxYears())->findFirst([
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

            $this->saveFormDeleted('Tax year has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (\Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function downloadAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        $model = (new TaxYears())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Downloaded by ' . $this->getUserFullName()
            );
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

        $model = (new TaxYears())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle(
            'Editing the tax year' .
            ($model->code ? ' (' . $model->code . ')' : '')
        );

        return $this->view->partial(
            'wako/tax_years/edit',
            [
                'data' => $model,
                'stats' => $this->editStats($model->id)
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Our tax years');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'tax_year_start',
                'created_by',
                'status'
            ],
            'tax_year_start'
        );


        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(TaxYears::class)
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'code' => '%' . $this->search . '%',
                'search_tags' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('code LIKE :code: OR search_tags LIKE :search_tags:');
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
            'wako/tax_years/index',
            [
                'data' => $paginator->paginate()
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new TaxYears())->findFirst([
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

            $this->saveFormUpdated('Tax year has been recovered');
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

            $model = $this->setData(new TaxYears());
            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to add the tax year',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved('Tax year has been saved');
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
        $model->tax_year_start = !empty($_POST['tax_year_start']) ?
            DateHelper::sql($_POST['tax_year_start'], false) :
            null;
        $model->tax_year_end = !empty($_POST['tax_year_end']) ? DateHelper::sql($_POST['tax_year_end'], false) : null;
        $model->code = $_POST['code'];
        $model->status = !empty($_POST['status']) ? $this->isValidStatus($_POST['status']) : $this->default_status;
        $model->dividend_allowance = !empty($_POST['dividend_allowance']) ?
            NumberHelper::fromCurrency($_POST['dividend_allowance']) : 0;
        $model->dividend_allowance_tax = !empty($_POST['dividend_allowance_tax']) ?
            $_POST['dividend_allowance_tax'] : 0;
        $model->return_due_by = !empty($_POST['return_due_by']) ?
            DateHelper::sql($_POST['return_due_by'], false) : null;

        if ($model->status == 'current') {
            $this
                ->modelsManager
                ->executeQuery(
                    'UPDATE ' . TaxYears::class . ' SET status="ended" WHERE tax_year_start <= :tax_year_start:',
                    [
                        'tax_year_start' => $model->tax_year_start
                    ]
                );
        }

        $model = $this->addSearchTags($model);

        return $model;
    }

    public function editStats($id)
    {
        $params = [
            'tax_year_id' => $id
        ];

        $stats = [
            'incoming' => 0,
            'outgoing' => 0
        ];

        return $stats;

        $model = new StatementItems();
        $table = $model->getSource();
        $table_invoices = (new StatementItemInvoices())->getSource();
        $table_receipts = (new StatementItemReceipts())->getSource();
        $query = "SELECT (
            SELECT
                SUM(`in`)
            FROM 
                $table
            LEFT JOIN 
                $table_invoices 
                ON 
                    $table_invoices.statement_item_id=$table.id AND $table_invoices.deleted_at IS NULL
            LEFT JOIN 
                $table_receipts 
                ON 
                    $table_receipts.statement_item_id=$table.id AND $table_receipts.deleted_at IS NULL
            WHERE 
                $table.deleted_at IS NULL AND
                $table_receipts.receipt_id IS NULL AND
                $table_invoices.invoice_id IS NULL AND
                tax_year_id = :tax_year_id
            LIMIT 1
        ) AS incoming,(
            SELECT
                SUM(`out`)
            FROM 
                $table
            LEFT JOIN 
                $table_invoices 
                ON 
                    $table_invoices.statement_item_id=$table.id AND $table_invoices.deleted_at IS NULL
            LEFT JOIN 
                $table_receipts 
                ON 
                    $table_receipts.statement_item_id=$table.id AND $table_receipts.deleted_at IS NULL
            WHERE 
                $table.deleted_at IS NULL AND
                $table_receipts.receipt_id IS NULL AND
                $table_invoices.invoice_id IS NULL AND
                tax_year_id = :tax_year_id
            LIMIT 1
        ) AS outgoing,";

        $result = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','), $params)
        ))->toArray()[0];

        if ($result) {
            $stats['incoming'] += $result['incoming'];
            $stats['outgoing'] += $result['outgoing'];
        }

        $model = new Receipts();
        $table = $model->getSource();
        $query = "SELECT (
            SELECT
                SUM(`amount`)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND
                tax_year_id = :tax_year_id
        ) AS outgoing";

        $result = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','), $params)
        ))->toArray()[0];

        if ($result) {
            $stats['outgoing'] += $result['outgoing'];
        }

        $model = new Invoices();
        $table = $model->getSource();
        $query = "SELECT (
            SELECT
                SUM(`amount`)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND
                tax_year_id = :tax_year_id AND
                status = 'paid'
        ) AS incoming";

        $result = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','), $params)
        ))->toArray()[0];

        if ($result) {
            $stats['incoming'] += $result['incoming'];
        }

        return $stats;
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new TaxYears())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $url = $this->global_url . '/edit/' . $model->id;

        try {
            $this->validate();
            $model = $this->setData($model);
            
            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the tax year',
                    $model->getMessages()
                );
            }
            
            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Tax year has been updated');

            $this->clearFormData();
            $this->redirect(UrlHelper::backend($url));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($url));
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
            'code',
            new PresenceOf(
                [
                    'message' => 'The code is required',
                ]
            )
        );

        $validation->add(
            'tax_year_start',
            new PresenceOf(
                [
                    'message' => 'The tax year start is required',
                ]
            )
        );

        $validation->add(
            'tax_year_end',
            new PresenceOf(
                [
                    'message' => 'The tax year end is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
