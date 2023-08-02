<?php

/**
 * Timesheets controller.
 *
 * @package     Kytschi\Mai\Controllers\TimesheetsController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Mai\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Projects;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Mai\Models\Timesheets;
use Kytschi\Wako\Models\Invoices;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class TimesheetsController extends ControllerBase
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
        'hr-manager'
    ];

    public $global_url = '/timesheets';
    public $resource = 'timesheet';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->hrs . $this->global_url;
    }

    public function addAction()
    {
        $this->secure();
        $this->setPageTitle('Adding a timesheet entry');

        return $this->view->partial(
            'mai/timesheets/add',
            [
                'projects' => Projects::find(['conditions' => 'deleted_at IS NULL'])
            ]
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Timesheets())->findFirst([
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

            $this->saveFormDeleted('Timesheet has been deleted');
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

        $model = (new Timesheets())->findFirst([
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
            'mai/timesheets/edit',
            [
                'data' => $model,
                'projects' => Projects::find(['conditions' => 'deleted_at IS NULL'])
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('My timesheets');
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
            ->from(Timesheets::class)
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
            'mai/timesheets/index',
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

        $model = (new Timesheets())->findFirst([
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

            $this->saveFormUpdated('Timesheet has been recovered');
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

            $model = $this->setData(new Timesheets());

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the timesheet',
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

            $this->saveFormSaved('Timesheet has been saved');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/create'));
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
        $model->summary = !empty($_POST['summary']) ? $_POST['summary'] : '';
        $model->status = !empty($_POST['status']) ? $this->isValidStatus($_POST['status']) : $this->default_status;
        $model->project_id = !empty($_POST['project_id']) ? $_POST['project_id'] : null;
        $model->period_start = !empty($_POST['period_start']) ? DateHelper::sql($_POST['period_start'], false) : null;
        $model->period_end = !empty($_POST['period_end']) ? DateHelper::sql($_POST['period_end'], false) : null;

        $model = $this->addSearchTags($model);

        return $model;
    }

    public function stats()
    {
        $table = (new Timesheets())->getSource();

        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE status = 'active') AS active,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'pending') AS pending,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'rejected') AS rejected,";
        $query .= "(SELECT count(id) FROM $table WHERE deleted_at IS NOT NULL) AS deleted,";

        $model = new Timesheets();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Timesheets())->findFirst([
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
                    'Failed to update the timesheet',
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

            $this->saveFormUpdated('Timesheet has been updated');

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

    public function updateAmount($id)
    {
        if (empty($id)) {
            return;
        }

        $model = (new Timesheets())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $id
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $amount = 0;
        foreach ($model->entries as $entry) {
            $amount += $entry->price;
        }

        $model->amount = $amount;
        if ($model->update() === false) {
            throw new SaveException(
                'Failed to update the timesheet',
                $model->getMessages()
            );
        }

        if ($model->project_id) {
            if (
                $results = Invoices::find([
                    'conditions' => 'project_id = :project_id: AND printed_at IS NULL AND emailed_at IS NULL',
                    'bind' => [
                        'project_id' => $model->project_id
                    ]
                ])
            ) {
                foreach ($results as $result) {
                    $amount = 0;
                    foreach ($result->timesheets as $timesheet) {
                        foreach ($timesheet->entries as $entry) {
                            $amount += $entry->price;
                        }
                    }

                    $result->amount = $amount;
                    if ($result->update() === false) {
                        throw new SaveException(
                            'Failed to update an invoice associated with this timesheet',
                            $result->getMessages()
                        );
                    }
                }
            }
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
