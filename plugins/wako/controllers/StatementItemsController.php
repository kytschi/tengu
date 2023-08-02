<?php

/**
 * Statement Items controller.
 *
 * @package     Kytschi\Wako\Controllers\StatementItemsController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Wako\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\NumberHelper;
use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Projects;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Wako\Controllers\DividendsController;
use Kytschi\Wako\Controllers\ReceiptsController;
use Kytschi\Wako\Models\Dividends;
use Kytschi\Wako\Models\Invoices;
use Kytschi\Wako\Models\Receipts;
use Kytschi\Wako\Models\StatementItems;
use Kytschi\Wako\Models\StatementItemInvoices;
use Kytschi\Wako\Models\StatementItemReceipts;
use Kytschi\Wako\Models\Statements;
use Kytschi\Wako\Traits\TaxYear;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Encryption\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Smalot\PdfParser\Parser;

class StatementItemsController extends ControllerBase
{
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

    public $global_url = '/statements/';
    public $resource = 'statement-item';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->fms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);

        $model = (new Statements())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('statement_id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle('Adding a statement item');

        return $this->view->partial(
            'wako/statements/items/add',
            [
                'projects' => Projects::find(['order' => 'name']),
                'statement' => $model
            ]
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new StatementItems())->findFirst([
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

            $url = $this->global_url . $this->dispatcher->getParam('statement_id') . '/items/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Statement item has been deleted');
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

        $model = (new StatementItems())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle('Edit the statement item');

        return $this->view->partial(
            'wako/statements/items/edit',
            [
                'data' => $model,
                'projects' => Projects::find(['order' => 'name']),
                'users' => (new Users())->find([
                    'conditions' => 'group_id IN("' . implode('","', (new DividendsController())->getGroups()) . '")',
                ])
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new StatementItems())->findFirst([
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

            $url = $this->global_url . $this->dispatcher->getParam('statement_id') . '/items/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Statement item has been recovered');
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

        $url = $this->global_url . $this->dispatcher->getParam('statement_id') . '/items';
        if (!empty($_GET['from'])) {
            $url = UrlHelper::append($url, 'from=' . $_GET['from']);
        }

        try {
            $this->validate();

            $model = $this->setData(new StatementItems());
            $model->statement_id = $this->dispatcher->getParam('statement_id');
            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to add the statement item',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);
            $this->saveDividend($model);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved('Statement item has been saved');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($url . '/add'));
        } catch (Exception $err) {
            if (!empty($model)) {
                $model->delete();
            }
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function setData($model)
    {
        $invoice_query = '';
        
        $model->description = $_POST['description'];
        $model->taxable = !empty($_POST['taxable']) ? true : false;
        $model->processed_at = !empty($_POST['processed_at']) ? DateHelper::sql($_POST['processed_at'], false) : null;
        $model->project_id = !empty($_POST['project_id']) ? $_POST['project_id'] : null;
        $model->balance = !empty($_POST['balance']) ? floatval($_POST['balance']) : 0.00;
        $model->shareholder_id = !empty($_POST['shareholder_id']) ? $_POST['shareholder_id'] : null;
        $model->employee_id = !empty($_POST['employee_id']) ? $_POST['employee_id'] : null;
        $model->expenses_employee_id = !empty($_POST['expenses_employee_id']) ? $_POST['expenses_employee_id'] : null;

        $model = $this->addSearchTags($model);
                
        if ($_POST['direction'] == 'outgoing') {
            $model->out = NumberHelper::fromCurrency($_POST['amount']);
            $model->in = 0.00;
        } elseif ($_POST['direction'] == 'incoming') {
            $model->in = NumberHelper::fromCurrency($_POST['amount']);
            $model->out = 0.00;
        } else {
            $model->in = 0.00;
            $model->out = 0.00;
        }

        if (!empty($model->statement)) {
            $model->currency = $model->statement->currency;
            if (!empty($model->statement->period_from)) {
                $model->statement->tax_year_id = $this->getTaxYearId($model->statement->period_from);
            }
    
            if (!empty($model->statement->period_to)) {
                $model->statement->tax_year_id = $this->getTaxYearId($model->statement->period_to);
            }

            if ($model->taxable) {
                $model->statement->taxable = 1;
            }
            if ($model->statement->update() === false) {
                throw new SaveException(
                    'Failed to update the statement',
                    $model->statement->getMessages()
                );
            }
        }

        if (!empty($model->processed_at)) {
            $model->tax_year_id = $this->getTaxYearId($model->processed_at);
        }

        if (!empty($_POST['receipts_id'])) {
            $table = (new StatementItemReceipts())->getSource();
            $table_receipts = (new Receipts())->getSource();
            $user_id = self::getUserId();
            $params = [
                ':statement_item_id' => $model->id,
                ':created_at' => date('Y-m-d H:i:s'),
                ':created_by' => $user_id,
                ':updated_at' => date('Y-m-d H:i:s'),
                ':updated_by' => $user_id,
                ':amount' => NumberHelper::fromCurrency($_POST['amount']),
                ':currency' => $model->currency
            ];
            $query = '';

            foreach ($_POST['receipts_id'] as $key => $id) {
                $sync = !empty($_POST['receipts_sync'][$key]) ? true : false;
                $params = array_merge(
                    $params,
                    [
                        ':id_' . $key => (new Random())->uuid(),
                        ':receipt_id_' . $key => $id,
                        ':sync_' . $key => $sync,
                    ]
                );

                if ($sync) {
                    $query .= 'UPDATE ' . $table_receipts . ' SET amount=:amount WHERE id=:receipt_id_' . $key . ';';
                }

                $query .= '
                        INSERT INTO ' . $table .
                        '(
                            id, 
                            statement_item_id, 
                            receipt_id,
                            sync,
                            created_at, 
                            created_by, 
                            updated_at, 
                            updated_by
                        )
                        SELECT
                            :id_' . $key . ',
                            :statement_item_id,
                            :receipt_id_' . $key . ',
                            :sync_' . $key . ',
                            :created_at,
                            :created_by,
                            :updated_at,
                            :updated_by
                        FROM DUAL WHERE NOT EXISTS
                        (
                            SELECT 
                                id, 
                                statement_item_id, 
                                receipt_id,
                                sync,
                                created_at, 
                                created_by, 
                                updated_at, 
                                updated_by
                            FROM ' . $table . '
                            WHERE statement_item_id=:statement_item_id AND receipt_id=:receipt_id_' . $key . ' 
                        );
                    UPDATE ' . $table . '
                    SET 
                        deleted_at=NULL,
                        deleted_by=NULL,
                        sync=:sync_' . $key . ' 
                    WHERE statement_item_id=:statement_item_id AND receipt_id=:receipt_id_' . $key . ';';
            }

            if (!empty($query)) {
                $this->db->query(
                    $query,
                    $params
                );
            }
        }

        if (!empty($_POST['invoices_id'])) {
            $table = (new StatementItemInvoices())->getSource();
            $table_invoice = (new Invoices())->getSource();
            $user_id = self::getUserId();
            $params = [
                ':paid_on' => $model->processed_at,
                ':statement_item_id' => $model->id,
                ':created_at' => date('Y-m-d H:i:s'),
                ':created_by' => $user_id,
                ':updated_at' => date('Y-m-d H:i:s'),
                ':updated_by' => $user_id
            ];
            $query = '';

            foreach ($_POST['invoices_id'] as $key => $id) {
                $params = array_merge(
                    $params,
                    [
                        ':id_' . $key => (new Random())->uuid(),
                        ':invoice_id_' . $key => $id
                    ]
                );

                $query .= 'UPDATE ' . $table_invoice . ' 
                    SET status="paid", paid_on=:paid_on WHERE id = :invoice_id_' . $key . ';';

                $query .= '
                        INSERT INTO ' . $table .
                        '(
                            id, 
                            statement_item_id, 
                            invoice_id,
                            created_at, 
                            created_by, 
                            updated_at, 
                            updated_by
                        )
                        SELECT
                            :id_' . $key . ',
                            :statement_item_id,
                            :invoice_id_' . $key . ',
                            :created_at,
                            :created_by,
                            :updated_at,
                            :updated_by
                        FROM DUAL WHERE NOT EXISTS
                        (
                            SELECT 
                                id, 
                                statement_item_id, 
                                invoice_id,
                                created_at, 
                                created_by, 
                                updated_at, 
                                updated_by
                            FROM ' . $table . '
                            WHERE statement_item_id=:statement_item_id AND invoice_id=:invoice_id_' . $key . ' 
                        );
                    UPDATE ' . $table . '
                    SET 
                        deleted_at=NULL,
                        deleted_by=NULL 
                    WHERE statement_item_id=:statement_item_id AND invoice_id=:invoice_id_' . $key . ';';
            }

            if (!empty($query)) {
                $this->db->query(
                    $query,
                    $params
                );
            }
        }

        return $model;
    }

    private function saveDividend($model)
    {
        if (!empty($_POST['shareholder_id'])) {
            $dividend = (new Dividends())->findFirst(
                [
                    'conditions' => 'shareholder_id = :shareholder_id: AND statement_item_id = :statement_item_id:',
                    'bind' => [
                        'shareholder_id' => $_POST['shareholder_id'],
                        'statement_item_id' => $model->id
                    ]
                ]
            );

            if (empty($dividend)) {
                $dividend = new Dividends([
                    'shareholder_id' => $_POST['shareholder_id'],
                    'statement_item_id' => $model->id,
                ]);

                $dividend->number = $dividend->getLatestNumber();
            }

            $dividend->amount = $model->out;
            $dividend->paid_on = $model->processed_at;
            $dividend->issued_on = $model->processed_at;
            $dividend->tax_year_id = $model->tax_year_id;
            $dividend->status = 'paid';

            if ($dividend->save() === false) {
                throw new SaveException(
                    'Failed to save the dividend',
                    $dividend->getMessages()
                );
            }

            (new DividendsController())->generate($dividend);
        }
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new StatementItems())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $url = $this->global_url . $this->dispatcher->getParam('statement_id') . '/items/edit/' . $model->id;
        if (!empty($_GET['from'])) {
            $url = UrlHelper::append($url, 'from=' . $_GET['from']);
        }

        try {
            $this->validate();
            
            $model = $this->setData($model);

            if ($_POST['receipt_name']) {
                $receipt = (new ReceiptsController())->save('receipt_');
                $model->receipt_id = $receipt->id;
            }

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the statement item',
                    $model->getMessages()
                );
            }
            
            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);
            $this->saveDividend($model);
                          
            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Statement item has been updated');
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
            'description',
            new PresenceOf(
                [
                    'message' => 'The description is required',
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

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
