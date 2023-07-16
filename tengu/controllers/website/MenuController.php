<?php

/**
 * Menu controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\MenuController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Website;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Menu;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Pagination;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class MenuController extends ControllerBase
{
    use Form;
    use Logs;
    use Pagination;

    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url = '/menu';
    public $resource = 'menu';

    private $targets = [
        'main',
        'footer',
        'both'
    ];

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Add a menu item');

        $pages = (new Pages())->find([
            'conditions' => 'deleted_at IS NULL',
            'order' => 'name ASC'
        ]);

        return $this->view->partial(
            'website/menu/add',
            [
                'statuses' => $this->defaultStatuses(),
                'pages' => $pages,
                'targets' => $this->targets
            ]
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $id = $this->dispatcher->getParam('id');

        if ($id == 'all') {
            if (!empty($_POST['selected'])) {
                $this
                    ->modelsManager
                    ->executeQuery(
                        'UPDATE ' .
                        Menu::class .
                        ' SET deleted_at=NOW(), deleted_by=:deleted_by:, status="deleted" WHERE id IN (:ids:)',
                        [
                            'deleted_by' => self::getUserId(),
                            'ids' => implode(',', $_POST['selected'])
                        ]
                    );
            }

            $this->saveFormDeleted('Menu items have been marked as deleted');
            $this->redirect(UrlHelper::backend($this->global_url));
        }

        $model = (new Menu())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $id
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

            $this->saveFormDeleted('Menu item has been marked as deleted');

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->redirect(UrlHelper::backend($url));
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
        $this->setPageTitle('Edit the menu item');

        $model = (new Menu())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $pages = (new Pages())->find([
            'conditions' => 'deleted_at IS NULL',
            'order' => 'name ASC'
        ]);

        return $this->view->partial(
            'website/menu/edit',
            [
                'data' => $model,
                'statuses' => $this->defaultStatuses(),
                'pages' => $pages,
                'targets' => $this->targets
            ]
        );
    }

    public static function get($target = 'both')
    {
        return (new Menu())->find([
            'conditions' => 'deleted_at IS NULL AND status = "active" AND (target = :target: OR target = "both")',
            'order' => 'sort ASC',
            'bind' => ['target' => $target]
        ]);
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Menu');
        $this->savePagination();
        $this->setFilters();

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Menu::class)
            ->where('id != ""')
            ->orderBy('sort ASC');

        if (!empty($this->search)) {
            $builder
                ->andWhere('name LIKE :name:')
                ->setBindParams(
                    [
                        'name' => '%' . $this->search . '%'
                    ]
                );
        }

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        return $this->view->partial(
            'website/menu/index',
            [
                'data' => $paginator->paginate()
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Menu())->findFirst([
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

            $this->saveFormUpdated('Menu item has been recovered');

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->redirect(UrlHelper::backend($url));
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

            $model = $this->setData(new Menu());
            $model->status = 'active';

            $menu = new Menu();
            $sort = (new \Phalcon\Mvc\Model\Resultset\Simple(
                null,
                $menu,
                $menu->getReadConnection()->query('SELECT count(id) AS sort FROM menu')
            ))->toArray()[0]['sort'];

            $model->sort = $sort;

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the menu item',
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
        $model->page_id = !empty($_POST['page_id']) ? $_POST['page_id'] : '';
        $model->name = strip_tags($_POST['name']);
        $model->tooltip = strip_tags($_POST['tooltip']);
        $model->external_link = !empty($_POST['external_link']) ? $_POST['external_link'] : '';
        $model->new_window = isset($_POST['new_window']) ? true : false;
        $model->status = isset($_POST['status']) ? 'active' : 'inactive';
        $model->target = $_POST['target'];

        if ($model->status != 'deleted') {
            $model->deleted_at = null;
            $model->deleted_by = null;
        }

        return $model;
    }

    public function sortAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        try {
            $sort = intval($this->dispatcher->getParam('sort'));
            if ($sort < 0) {
                $sort *= -1;
            }

            $dir = $this->dispatcher->getParam('dir');
            if ($dir == 'up') {
                $query = 'UPDATE menu SET sort = sort + 1 WHERE sort=:sort AND id != :id';
            } else {
                $query = 'UPDATE menu SET sort = sort - 1 WHERE sort=:sort AND id != :id';
            }

            $query .= '; UPDATE menu SET sort = :sort WHERE id = :id';

            $this
                ->db
                ->query(
                    $query,
                    [
                        ':sort' => $sort,
                        ':id' => $this->dispatcher->getParam('id')
                    ]
                );

            $this->saveFormSaved('Menu items have been sorted');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function stats()
    {
        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM menu WHERE status = 'active') AS active,";
        $query .= "(SELECT count(id) FROM menu WHERE status = 'inactive') AS inactive,";
        $query .= "(SELECT count(id) FROM menu WHERE deleted_at IS NOT NULL) AS deleted,";

        $model = new Menu();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Menu())->findFirst([
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
                    'Failed to update the menu item',
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
            'tooltip',
            new PresenceOf(
                [
                    'message' => 'The tooltip is required',
                ]
            )
        );

        $validation->add(
            'target',
            new PresenceOf(
                [
                    'message' => 'The target is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
