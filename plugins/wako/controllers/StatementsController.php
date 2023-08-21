<?php

/**
 * Statements controller.
 *
 * @package     Kytschi\Wako\Controllers\StatementsController
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

use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\NumberHelper;
use Kytschi\Tengu\Helpers\StringHelper;
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
use Kytschi\Wako\Models\StatementItems;
use Kytschi\Wako\Models\Statements;
use Kytschi\Wako\Traits\TaxYear;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Encryption\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Smalot\PdfParser\Parser;

class StatementsController extends InvoicesController
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

    public $global_url = '/statements';
    public $resource = 'statement';
    public $valid_files = [
        'application/pdf'
    ];

    private $parsers = [
        'barclays-business' => 'Kytschi\Wako\Parsers\BarclaysBusinessParser'
    ];

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->fms . $this->global_url;
        $this->orderDir = 'DESC';
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Adding a statement');

        return $this->view->partial(
            'wako/statements/add'
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Statements())->findFirst([
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

            $this
                ->modelsManager
                ->executeQuery(
                    'UPDATE ' . StatementItems::class . ' 
                    SET deleted_at = NOW(), deleted_by = :user_id: 
                    WHERE statement_id = :statement_id:',
                    [
                        'statement_id' => $model->id,
                        'user_id' => self::getUserId()
                    ]
                );

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

            $this->saveFormDeleted('Statement has been deleted');
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

        $model = (new Statements())->findFirst([
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
            'wako/statements/edit',
            [
                'data' => $model
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Our statements');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'name',
                'period_from',
                'status'
            ],
            'period_from'
        );

        $params = [];

        if (empty($this->search)) {
            $builder = $this
                ->modelsManager
                ->createBuilder()
                ->from(Statements::class)
                ->orderBy($this->orderBy . ' ' . $this->orderDir);
        } else {
            $this->setIndexDefaults(
                [
                    'name',
                    'processed_at',
                    'status'
                ],
                'processed_at'
            );
            
            $builder = $this
                ->modelsManager
                ->createBuilder()
                ->from(StatementItems::class)
                ->orderBy($this->orderBy . ' ' . $this->orderDir . ', statement_id');
            
            $params = [
                'search_tags' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('search_tags LIKE :search_tags:');
        }

        $tax_year = $this->getCurrentTaxYear();

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
                    case 'taxable':
                        $builder->andWhere('taxable = :taxable:');
                        $params['taxable'] = ($value == 'yes' ? 1 : 0);
                        $iLoop++;
                        break;
                    case 'year':
                        if (empty($this->search)) {
                            $builder->andWhere('period_from >= :start: AND period_to <= :end:');
                            $params['start'] = $tax_year->tax_year_start;
                            $params['end'] = $tax_year->tax_year_end;
                        } else {
                            $builder->andWhere('processed_at BETWEEN :start: AND :end:');
                            $params['start'] = $tax_year->tax_year_start;
                            $params['end'] = $tax_year->tax_year_end;
                        }
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
            'wako/statements/index',
            [
                'data' => $paginator->paginate(),
                'stats' => $this->stats($tax_year->tax_year_start, $tax_year->tax_year_end),
                'tax_year' => $tax_year,
                'years' => $this->getTaxYears()
            ]
        );
    }

    private function parse($model, $type = '')
    {
        if (empty($model->file)) {
            return $model;
        }

        $name = StringHelper::random(64);
        file_put_contents('/tmp/' . $name, $this->readFile($model->file));

        shell_exec('pdftotext -raw /tmp/' . $name . ' /tmp/' . $name . '.tmp');
        $lines = explode("\n", file_get_contents('/tmp/' . $name . '.tmp'));
        unlink('/tmp/' . $name);
        unlink('/tmp/' . $name . '.tmp');

        if ($type) {
            $class = $this->parsers[$type];
            if ($parsed = (new $class())->parse($lines, $model, $type)) {
                $this->saveFormUpdated('Statement items have been processed');
                return $parsed;
            }
        } else {
            foreach ($this->parsers as $class) {
                if ($parsed = (new $class())->parse($lines, $model, $type)) {
                    $this->saveFormUpdated('Statement items have been processed');
                    return $parsed;
                }
            }
        }

        $this->saveFormWarning('No valid statement parser');
        return $model;
    }

    public function parseAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Statements())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model = $this->parse($model, $model->type);
            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the statement',
                    $model->getMessages()
                );
            }

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (\Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    private function parseBarclaysBusiness($lines, $model)
    {
        $record = false;
        $moneys = false;
        $new = false;
        $data = [];
        
        foreach ($lines as $line) {
            if (substr(strtolower($line), 0, strlen('Start balance')) == strtolower('Start balance')) {
                $moneys = true;
                $new = true;
                continue;
            }

            if (strtolower($line) == strtolower('Date Description Money out £ Money in £ Balance £')) {
                $record = true;
                $new = true;
                continue;
            }

            if (strtolower($line) == strtolower('Balance brought forward from previous page')) {
                $new = true;
                continue;
            }

            if ($moneys) {
                if (substr(strtolower($line), 0, strlen('Money out')) == strtolower('Money out')) {
                    $model->out = NumberHelper::fromCurrency($line);
                    continue;
                } elseif (substr(strtolower($line), 0, strlen('Money in')) == strtolower('Money in')) {
                    $model->in = NumberHelper::fromCurrency($line);
                    continue;
                }
            }

            if ($record) {
                if (strpos(strtolower($line), strtolower('Start balance')) !== false) {
                    continue;
                }
                if ($new) {
                    $arr = [];
                    $new = false;
                }
                $arr[] = $line;
                $checks = explode(' ', $line);

                if (isset($checks[1])) {
                    if (
                        is_numeric(NumberHelper::fromCurrency($checks[0])) &&
                        is_numeric(NumberHelper::fromCurrency($checks[1]))
                    ) {
                        $new = true;
                        $data[] = $arr;
                    }
                }
            }
        }

        if ($data) {
            $user_id = self::getUserId();

            $this
                ->modelsManager
                ->executeQuery(
                    'UPDATE ' .
                    StatementItems::class .
                    ' SET deleted_at=NOW(), deleted_by=:deleted_by: WHERE statement_id = :statement_id:',
                    [
                        'deleted_by' => $user_id,
                        'statement_id' => $model->id
                    ]
                );
            
            $table = (new StatementItems())->getSource();
            $params = [
                ':statement_id' => $model->id,
                ':created_at' => date('Y-m-d H:i:s'),
                ':created_by' => $user_id,
                ':updated_at' => date('Y-m-d H:i:s'),
                ':updated_by' => $user_id
            ];

            $query = '';
            $ins = [
                'direct credit',
                'refund'
            ];
            $key = 0;
            foreach ($data as $arr) {
                $direction = 'out';
                foreach ($ins as $in_str) {
                    if (strpos(strtolower($arr[0]), $in_str) !== false) {
                        $direction = 'in';
                        break;
                    }
                }

                $in = 0;
                $out = 0;
                $amounts = explode(' ', array_pop($arr));
                if ($direction == 'in') {
                    $in = NumberHelper::fromCurrency($amounts[0]);
                } else {
                    $out = NumberHelper::fromCurrency($amounts[0]);
                }
                $balance = NumberHelper::fromCurrency($amounts[1]);

                $description = implode("\n", $arr);

                $params = array_merge(
                    $params,
                    [
                        ':id_' . $key => (new Random())->uuid(),
                        ':description_' . $key => self::encrypt($description),
                        ':in_' . $key => $in,
                        ':out_' . $key => $out,
                        ':balance_' . $key => $balance
                    ]
                );
    
                $query .= '
                    INSERT INTO ' . $table .
                    ' (id, statement_id, description, `in`, `out`, `balance`, created_at, created_by, updated_at, updated_by)
                    SELECT
                        :id_' . $key . ',
                        :statement_id,
                        :description_' . $key . ',
                        :in_' . $key . ',
                        :out_' . $key . ',
                        :balance_' . $key . ',
                        :created_at,
                        :created_by,
                        :updated_at,
                        :updated_by
                    FROM DUAL WHERE NOT EXISTS
                    (
                        SELECT 
                            id, 
                            statement_id, 
                            description, 
                            `in`, 
                            `out`, 
                            `balance`, 
                            created_at, 
                            created_by, 
                            updated_at, 
                            updated_by
                        FROM ' . $table . '
                        WHERE statement_id=:statement_id AND description=:description_' . $key . ' 
                    );
                UPDATE ' . $table . '
                SET 
                    deleted_at=NULL,
                    deleted_by=NULL, 
                    balance=:balance_' . $key . ', 
                    `in`=:in_' . $key . ', 
                    `out`=:out_' . $key . ' 
                WHERE statement_id=:statement_id AND description=:description_' . $key . ';';
                $key++;
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

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Statements())->findFirst([
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
            $this
                ->modelsManager
                ->executeQuery(
                    'UPDATE ' . StatementItems::class . ' 
                    SET deleted_at=NULL,deleted_by=NULL
                    WHERE statement_id = :statement_id:',
                    [
                        'statement_id' => $model->id
                    ]
                );

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

            $this->saveFormUpdated('Statement has been recovered');
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

            $model = $this->setData(new Statements());
            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to add the statement',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);

            if (!empty($_FILES['upload_statement']['name'])) {
                $this->validFile($_FILES['upload_statement']);

                $this->addFile(
                    $model->id,
                    $_FILES['upload_statement'],
                    $this->resource,
                    $model->name,
                    '',
                    false,
                    true
                );

                $model = $this->parse($model);
                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the statement',
                        $model->getMessages()
                    );
                }
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved('Statement has been saved');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/add'));
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
        $model->name = $_POST['name'];
        $model->in = !empty($_POST['in']) ? floatval($_POST['in']) : 0;
        $model->out = !empty($_POST['out']) ? floatval($_POST['out']) : 0;
        $model->period_from = !empty($_POST['period_from']) ? DateHelper::sql($_POST['period_from'], false) : null;
        $model->period_to = !empty($_POST['period_to']) ? DateHelper::sql($_POST['period_to'], false) : null;
        $model->status = !empty($_POST['status']) ? $this->isValidStatus($_POST['status']) : $this->default_status;
        $model->taxable = 0;

        $model = $this->addSearchTags($model);

        if (!empty($model->period_from)) {
            $model->tax_year_id = $this->getTaxYearId($model->period_from);
        }

        if (!empty($model->period_to)) {
            $model->tax_year_id = $this->getTaxYearId($model->period_to);
        }

        if ($model->items) {
            $items = (new StatementItems())->findFirst([
                'conditions' => 'statement_id = :statement_id: AND deleted_at IS NULL AND taxable = 1',
                'bind' => [
                    'statement_id' => $model->id
                ]
            ]);
            if (!empty($items)) {
                $model->taxable = 1;
            }
        }

        return $model;
    }

    public function stats($start, $end)
    {
        $model = new StatementItems();
        $table = $model->getSource();

        $params = [
            'outgoing_start' => $start,
            'outgoing_end' => $end,
            'incoming_start' => $start,
            'incoming_end' => $end
        ];

        $query = "SELECT 
        (
            SELECT
                SUM(`out`)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND 
                (processed_at BETWEEN :outgoing_start AND :outgoing_end)
        ) AS outgoing,
        (
            SELECT
                SUM(`in`)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND 
                (processed_at BETWEEN :incoming_start AND :incoming_end)
        ) AS incoming";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','), $params)
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Statements())->findFirst([
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
            
            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);
            
            if (!empty($_FILES['upload_statement']['name'])) {
                $this->validFile($_FILES['upload_statement']);
                $this->clearFiles($this->resource, $model->id);
                
                $this->addFile(
                    $model->id,
                    $_FILES['upload_statement'],
                    $this->resource,
                    $model->name,
                    '',
                    false,
                    true
                );

                $model = $this->parse($model);
                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the statement',
                        $model->getMessages()
                    );
                }
            }

            foreach ($model->items as $item) {
                $item->taxable = !empty($_POST['taxable'][$item->id]) ? true : false;
                $item->tax_year_id = $this->getTaxYearId($item->processed_at);
                if ($item->update() === false) {
                    throw new SaveException(
                        'Failed to update the statement item',
                        $item->getMessages()
                    );
                }

                if ($item->taxable) {
                    $model->taxable = 1;
                }
            }

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the statement',
                    $model->getMessages()
                );
            }
              
            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Statement has been updated');

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
