<?php

/**
 * Receipts controller.
 *
 * @package     Kytschi\Wako\Controllers\ReceiptsController
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
use Kytschi\Tengu\Traits\Core\Api;
use Kytschi\Tengu\Traits\Core\Currency;
use Kytschi\Tengu\Models\Core\Projects;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Wako\Controllers\InvoicesController;
use Kytschi\Wako\Controllers\SettingsController;
use Kytschi\Wako\Models\Receipts;
use Kytschi\Wako\Traits\TaxYear;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class ReceiptsController extends InvoicesController
{
    use Api;
    use Currency;
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

    public $global_url = '/receipts';
    public $resource = 'receipt';
    public $valid_files = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];

    private $parsers = [
        'amazon-uk' => 'Kytschi\Wako\Parsers\AmazonParser',
        'ebay' => 'Kytschi\Wako\Parsers\EbayParser',
        'ebuyer' => 'Kytschi\Wako\Parsers\EbuyerParser',
        'evri' => 'Kytschi\Wako\Parsers\EvriParser',
        'gandi-international' => 'Kytschi\Wako\Parsers\GandiParser',
        'giffgaff' => 'Kytschi\Wako\Parsers\GiffGaffParser',
        'linode' => 'Kytschi\Wako\Parsers\LinodeParser',
        'proton' => 'Kytschi\Wako\Parsers\ProtonParser',
        'scan' => 'Kytschi\Wako\Parsers\ScanParser',
        'wickes' => 'Kytschi\Wako\Parsers\WickesParser',
        'generic' => 'Kytschi\Wako\Parsers\GenericParser'
    ];

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->fms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Adding a receipt');

        return $this->view->partial(
            'wako/receipts/add',
            [
                'currencies' => $this->currencies,
                'projects' => Projects::find(['order' => 'name']),
            ]
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Receipts())->findFirst([
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

            $this->saveFormDeleted('Receipt has been deleted');
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

        $model = (new Receipts())->findFirst([
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
            'wako/receipts/edit',
            [
                'currencies' => $this->currencies,
                'data' => $model,
                'projects' => Projects::find(['order' => 'name']),
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();
        $this->orderDir = 'DESC';

        $this->secure($this->access);
        $this->setPageTitle('Our receipts');
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
            ->from(Receipts::class)
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
                        $builder->andWhere('issued_on BETWEEN :start: AND :end:');
                        $params['start'] = $tax_year->tax_year_start;
                        $params['end'] = $tax_year->tax_year_end;
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
            'wako/receipts/index',
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

        if ($type && !empty($this->parsers[$type])) {
            $class = $this->parsers[$type];
            if ($parsed = (new $class())->parse($lines, $model, $type)) {
                $this->saveFormUpdated('Receipt has been processed');
                return $parsed;
            }
        } else {
            foreach ($this->parsers as $class) {
                if ($parsed = (new $class())->parse($lines, $model, $type)) {
                    $this->saveFormUpdated('Receipt has been processed');
                    return $parsed;
                }
            }
        }
        
        $this->saveFormWarning('No valid receipt parser');
        return null;
    }

    public function parseAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Receipts())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            if ($model = $this->parse($model)) {
                $model = $this->addSearchTags($model);
                if (!empty($model->issued_on)) {
                    $model->tax_year_id = $this->getTaxYearId($model->issued_on);
                }
                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the receipt',
                        $model->getMessages()
                    );
                }
            }

            $url = $this->global_url . '/edit/' . $this->dispatcher->getParam('id');
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

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Receipts())->findFirst([
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

            $this->saveFormUpdated('Receipt has been recovered');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (\Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function save($key = '')
    {
        $this->validate($key);

        $model = $this->setData(new Receipts(), $key);
        if ($model->save() === false) {
            throw new SaveException(
                'Failed to add the receipt',
                $model->getMessages()
            );
        }

        $this->addTagsFromRequest($model->id, true);

        if (!empty($_FILES['upload_receipt']['name'])) {
            $this->validFile($_FILES['upload_receipt']);
            $this->addFile(
                $model->id,
                $_FILES['upload_receipt'],
                $this->resource,
                $model->name,
                '',
                false,
                true
            );

            $model = $this->parse($model);
            if (!empty($model->issued_on)) {
                $model->tax_year_id = $this->getTaxYearId($model->issued_on);
            }
            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the receipt',
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

        $this->saveFormSaved('Receipt has been saved');

        return $model;
    }

    public function saveAction()
    {
        $this->secure($this->access);

        try {
            $model = $this->save();
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
                'name' => '%' . $_POST['query'] . '%',
                'search_tags' => '%' . $_POST['query'] . '%'
            ];

            if (
                $results = (new Receipts())->find([
                    'conditions' => $query,
                    'bind' => $params,
                    'order' => 'issued_on DESC'
                ])
            ) {
                foreach ($results as $result) {
                    $obj = new \stdClass();
                    $obj->id = $result->id;
                    $obj->name = $result->name;
                    $obj->date = $result->issued_on;
                    $obj->type = !empty($result->file) ? $result->file->mime_type : '';
                    $obj->url = !empty($result->file) ? $result->file->output_url : '';
    
                    $return[] = $obj;
                }
            }
        }

        $this->apiResponse(
            'found receripts',
            $return
        );
    }

    public function setData($model, $key = '')
    {
        $model->name = $_POST[$key . 'name'];
        $model->amount = !empty($_POST[$key . 'amount']) ? NumberHelper::fromCurrency($_POST[$key . 'amount']) : 0.00;
        $model->vat = !empty($_POST[$key . 'vat']) ? NumberHelper::fromCurrency($_POST[$key . 'vat']) : 0.00;
        $model->sub_total = !empty($_POST[$key . 'sub_total']) ?
            NumberHelper::fromCurrency($_POST[$key . 'sub_total']) :
            $model->amount - $model->vat;
        $model->issued_on = !empty($_POST[$key . 'issued_on']) ?
            DateHelper::sql($_POST[$key . 'issued_on'], false) :
            null;
        $model->status = !empty($_POST[$key . 'status']) ?
            $this->isValidStatus($_POST[$key . 'status']) :
            $this->default_status;
        $model->taxable = !empty($_POST[$key . 'taxable']) ? true : false;
        $model->currency = !empty($_POST[$key . 'currency']) ? $_POST[$key . 'currency'] : 'GBP';

        if (!empty($model->issued_on)) {
            $model->tax_year_id = $this->getTaxYearId($model->issued_on);
        }

        $model = $this->addSearchTags($model);

        return $model;
    }

    public function stats($start, $end)
    {
        $model = new Receipts();
        $table = $model->getSource();

        $params = [
            'outgoing_taxable_start' => $start,
            'outgoing_taxable_end' => $end,
            'outgoing_vat_start' => $start,
            'outgoing_vat_end' => $end
        ];

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
                taxable = 1 AND 
                (issued_on BETWEEN :outgoing_taxable_start AND :outgoing_taxable_end)
                $search
        ) AS outgoing_taxable,
        (
            SELECT
                SUM(vat)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND
                taxable = 1 AND 
                (issued_on BETWEEN :outgoing_vat_start AND :outgoing_vat_end)
                $search
        ) AS outgoing_vat";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','), $params)
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Receipts())->findFirst([
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
                    'Failed to update the receipt',
                    $model->getMessages()
                );
            }
            
            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);
            
            if (!empty($_FILES['upload_receipt']['name'])) {
                $this->validFile($_FILES['upload_receipt']);
                $this->clearFiles($this->resource, $model->id);
                
                $this->addFile(
                    $model->id,
                    $_FILES['upload_receipt'],
                    $this->resource,
                    $model->name,
                    '',
                    false,
                    true
                );

                $model = $this->parse($model);
                if (!empty($model->issued_on)) {
                    $model->tax_year_id = $this->getTaxYearId($model->issued_on);
                }
                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the receipt',
                        $model->getMessages()
                    );
                }
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Receipt has been updated');

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

    public function validate($key = '')
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            $key . 'name',
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
