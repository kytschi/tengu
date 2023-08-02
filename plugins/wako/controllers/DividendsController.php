<?php

/**
 * Dividends controller.
 *
 * @package     Kytschi\Wako\Controllers\DividendsController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Wako\Controllers;

use Kytschi\Tengu\Controllers\Core\GroupsController;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Wako\Controllers\SettingsController;
use Kytschi\Wako\Models\Dividends;
use Kytschi\Wako\Traits\TaxYear;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Encryption\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Mpdf\Mpdf;

class DividendsController extends ControllerBase
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

    public $global_url = '/dividends';
    public $resource = 'dividend';

    public $include_groups = [
        'Super user',
        'Administrator',
    ];

    private $include_group_ids = [];

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->fms . $this->global_url;
        $this->orderDir = 'desc';
        $this->getGroups();
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Adding a dividend');

        return $this->view->partial(
            'wako/dividends/add',
            [
                
            ]
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Dividends())->findFirst([
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

            $this->saveFormDeleted('Dividend has been deleted');
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

        $model = (new Dividends())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }
        $this->setPageTitle('Editing the dividend');
        
        return $this->view->partial(
            'wako/dividends/edit',
            [
                'data' => $model,
                'users' => (new Users())->find([
                    'conditions' => 'group_id IN("' . implode('","', $this->include_group_ids) . '")',
                ])
            ]
        );
    }

    public function generate($model)
    {
        $view = new \Phalcon\Mvc\View();
        $view->setViewsDir(($this->di->getConfig())->application->pluginsDir . 'wako/views');
        $view->setVar('data', $model);
        $view->start();
        $view->render('wako/dividends/template', 'default');
        $view->finish();

        $filename = $file = ($this->di->getConfig())->application->tmpDir . (new Random())->uuid();
        
        $pdf = new mPDF([
            'tempDir' => ($this->di->getConfig())->application->tmpDir,
            'format' => 'A4',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0
        ]);
        $pdf->setAutoTopMargin = 'stretch';
        $pdf->setAutoBottomMargin = 'stretch';

        $pdf->WriteHTML(
            file_get_contents(($this->di->getConfig())->application->pluginsDir . 'wako/assets/css/wako.css'),
            \Mpdf\HTMLParserMode::HEADER_CSS
        );

        $pdf->WriteHTML($view->getContent(), \Mpdf\HTMLParserMode::HTML_BODY);

        $pdf->Output($filename, \Mpdf\Output\Destination::FILE);

        if (empty($model->file)) {
            $this->addFile(
                $model->id,
                $filename,
                $this->resource,
                'dividend',
                '',
                false,
                true
            );
        } else {
            file_put_contents(
                $model->file->location,
                self::encrypt(file_get_contents($filename))
            );
        }
        
        shell_exec('rm ' . $filename);
    }

    public function getGroups()
    {
        $controller = new GroupsController();
        foreach ($this->include_groups as $group) {
            if ($id = $controller->getGroup($group)) {
                $this->include_group_ids[] =  $id[0];
            }
        }
        return $this->include_group_ids;
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Our dividends');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'issued_on',
                'status'
            ],
            'issued_on'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Dividends::class)
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'search_tags' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('search_tags LIKE :search_tags:');
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
            'wako/dividends/index',
            [
                'data' => $paginator->paginate(),
                'statuses' => ['pending', 'paid'],
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

        $model = (new Dividends())->findFirst([
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

            $this->saveFormUpdated('Dividend has been recovered');
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

            $model = $this->setData(new Dividends());

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to add the dividend',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);
            $this->generate($model);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved('Dividend has been saved');
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
        $model->shareholder_id = $_POST['shareholder_id'];
        $model->issued_on = !empty($_POST['issued_on']) ? DateHelper::sql($_POST['issued_on'], false) : null;
        $model->paid_on = !empty($_POST['paid_on']) ? DateHelper::sql($_POST['paid_on'], false) : null;
        
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
            'paid_start' => $start,
            'paid_end' => $end,
            'pending_start' => $start,
            'pending_end' => $end
        ];

        $model = new Dividends();
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
                `status` = 'paid' AND 
                issued_on BETWEEN :paid_start AND :paid_end
        ) AS paid,
        (
            SELECT
                SUM(amount)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND 
                `status` = 'pending' AND 
                issued_on BETWEEN :pending_start AND :pending_end
                $search
        ) AS pending";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','), $params)
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Dividends())->findFirst([
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
                    'Failed to update the dividend',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);
            $this->generate($model);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Dividend has been updated');

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
            'shareholder_id',
            new PresenceOf(
                [
                    'message' => 'The shareholder is required',
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
