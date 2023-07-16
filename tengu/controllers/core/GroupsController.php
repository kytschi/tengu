<?php

/**
 * Groups controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\GroupsController
 * @copyright   2023 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Groups;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Pagination;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class GroupsController extends ControllerBase
{
    use Form;
    use Logs;
    use Pagination;

    public $access = [
        'super-user'
    ];

    public $global_url = '/groups';
    public $resource = 'group';

    public static $system = [
        'Super user' => [
            '00000000-0000-0000-0000-000000000001',
            'super-user'
        ],
        'Administrator' => [
            '00000000-0000-0000-0000-000000000002',
            'administrator'
        ],
        'Blogger' => [
            '00000000-0000-0000-0000-000000000003',
            'blogger'
        ],
        'Content writer' => [
            '00000000-0000-0000-0000-000000000004',
            'content-writer'
        ],
        'Customer' => [
            '00000000-0000-0000-0000-000000000005',
            'customer'
        ]
    ];

    public function initialize()
    {
        $this->getGroups();
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Add a group');

        return $this->view->partial(
            'core/groups/add',
            [
                'statuses' => $this->defaultStatuses()
            ]
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Groups())->findFirst([
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

            $this->saveFormDeleted('Group has been deleted');
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
        $this->setPageTitle('Editing the group');

        $model = (new Groups())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        return $this->view->partial(
            'core/groups/edit',
            [
                'data' => $model,
                'statuses' => $this->defaultStatuses()
            ]
        );
    }

    public function getGroups()
    {
        if (!empty(($this->di->getConfig())->apps)) {
            foreach (($this->di->getConfig())->apps as $app) {
                if (file_exists($file = ($this->di->getConfig())->application->pluginsDir . $app . '/config/groups.php')) {
                    include $file;
                    self::$system = array_merge(self::$system, $groups);
                }
            }
        }

        return self::$system;
    }

    public function getGroup($name)
    {
        $this->getGroups();

        foreach (self::$system as $group_name => $group) {
            if ($name == $group_name) {
                return $group;
            }
        }

        return [];
    }

    public function getGroupId($name)
    {
        if ($group = $this->getGroup($name)) {
            return $group[0];
        }
        return false;
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Our groups');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'name',
                'slug',
                'status'
            ],
            'name'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Groups::class)
            ->where('id != ""')
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'name' => '%' . $this->search . '%',
                'slug' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('name LIKE :name: OR slug LIKE :slug:');
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
            'core/groups/index',
            [
                'data' => $paginator->paginate(),
                'stats' => $this->stats()
            ]
        );
    }

    public static function isSystem($group_id)
    {
        foreach (self::$system as $name => $group) {
            if ($group_id == $group[0]) {
                return new Groups(
                    [
                        'id' => $group[0],
                        'name' => $name,
                        'slug' => $group[1],
                        'system' => true,
                        'status' => 'active',
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => '00000000-0000-0000-0000-000000000000',
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => '00000000-0000-0000-0000-000000000000'
                    ]
                );
            }
        }

        return null;
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Groups())->findFirst([
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

            $this->saveFormUpdated("You've recovered the group");
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (Exception $err) {
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

            $model = $this->setData(new Groups());

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the group',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved();
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/add'));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    private function setData($model)
    {
        $model->name = $_POST['name'];
        $model->slug = empty($_POST['slug']) ? $this->createSlug($_POST['name']) : $_POST['slug'];
        $model->system = false;
        $model->status = isset($_POST['status']) ? 'active' : 'inactive';

        if ($model->status != 'deleted') {
            $model->deleted_at = null;
            $model->deleted_by = null;
        }

        return $model;
    }

    public function stats()
    {
        $table = (new Groups())->getSource();
        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE status = 'active') AS active,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'inactive') AS inactive,";
        $query .= "(SELECT count(id) FROM $table WHERE deleted_at IS NOT NULL) AS deleted,";

        $model = new Groups();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function systemAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle("System's groups");

        $groups = [];
        foreach (self::$system as $name => $group) {
            $groups[] = new Groups(
                [
                    'id' => $group[0],
                    'name' => $name,
                    'slug' => $group[1],
                    'system' => true,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => '00000000-0000-0000-0000-000000000000',
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => '00000000-0000-0000-0000-000000000000'
                ]
            );
        }

        return $this->view->partial(
            'core/groups/system/index',
            [
                'data' => $groups
            ]
        );
    }

    public function updateAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Groups())->findFirst([
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
                    'Failed to update the group',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated();
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

    private function validate()
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
            'status',
            new PresenceOf(
                [
                    'message' => 'The status is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
