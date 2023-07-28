<?php

/**
 * Menu controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\MenuController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
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

namespace Kytschi\Tengu\Controllers\Website;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Controllers\Website\MenuCategoriesController;
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
    public $resource = 'menu-item';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    /**
     * Add action
     *
     * @param string $title The window title
     * @return HTML
     */
    public function addAction($title = 'Add a menu item')
    {
        $this->secure($this->access);
        $this->setPageTitle($title);

        $pages = (new Pages())->find([
            'conditions' => 'deleted_at IS NULL',
            'order' => 'name ASC'
        ]);

        return $this->view->partial(
            'website/menu/add',
            [
                'statuses' => $this->defaultStatuses(),
                'pages' => $pages,
                'url' => $this->global_url,
                'categories' => Menu::find([
                    'conditions' => 'type="menu-category"',
                    'order' => 'sort ASC'
                ])
            ]
        );
    }

    /**
     * Delete action
     *
     * @param string $id The id of the entry
     * @return void
     * @throws SaveException
     */
    public function deleteAction($id)
    {
        $this->clearFormData();

        $this->secure($this->access);

        if ($id == 'all') {
            if (!empty($_POST['selected'])) {
                $this
                    ->modelsManager
                    ->executeQuery(
                        'UPDATE ' .
                        Menu::class .
                        ' SET
                            deleted_at=NOW(),
                            deleted_by=:deleted_by:,
                            status="deleted" 
                        WHERE 
                            id IN (:ids:)',
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

    /**
     * Edit action
     *
     * @param string $id The id of the entry
     * @return HTML
     */
    public function editAction($id, $title = 'Edit the menu item')
    {
        $this->secure($this->access);
        $this->setPageTitle($title);

        $model = (new Menu())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $id
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
                'url' => $this->global_url,
                'categories' => Menu::find([
                    'conditions' => 'type="menu-category" AND id != :id:',
                    'bind' => ['id' => $model->id],
                    'order' => 'sort ASC'
                ])
            ]
        );
    }

    public static function get($target = 'both')
    {
        return (new Menu())->find([
            'conditions' => 'deleted_at IS NULL AND status = "active"',
            'order' => 'sort ASC'
        ]);
    }

    /**
     * Index action
     *
     * @param $title The window title
     * @param $template The template to be used
     * @return HTML
     */
    public function indexAction($title = 'Menu', $template = 'website/menu/index')
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle($title);
        $this->savePagination();
        $this->setFilters();

        $binds = [
            'type' => $this->resource
        ];

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Menu::class)
            ->where('type = :type:')
            ->orderBy('sort ASC');

        if (!empty($this->search)) {
            $binds['name'] = '%' . $this->search . '%';
            $builder
                ->andWhere('name LIKE :name:');
        }

        $builder->setBindParams($binds);

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        return $this->view->partial(
            $template,
            [
                'data' => $paginator->paginate(),
                'stats' => $this->stats(),
                'url' => $this->global_url
            ]
        );
    }

    /**
     * Recover action
     *
     * @param string $id The id of the entry
     * @return void
     * @throws SaveException
     */
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

    /**
     * Save action
     *
     * @param string $id The id of the entry
     * @return void
     * @throws ValidationException
     * @throws SaveException
     */
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

            (new MenuCategoriesController())->addCategories($model->id);

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

    /**
     * Set data
     *
     * @param Kytschi\Tengu\Models\Website\Menu $model The menu model
     * @return Kytschi\Tengu\Models\Website\Menu $model
     */
    private function setData($model)
    {
        $model->page_id = !empty($_POST['page_id']) ? $_POST['page_id'] : '';
        $model->slug = !empty($_POST['slug']) ? $_POST['slug'] : '';
        $model->name = strip_tags($_POST['name']);
        $model->tooltip = strip_tags($_POST['tooltip']);
        $model->external_link = !empty($_POST['external_link']) ? $_POST['external_link'] : '';
        $model->new_window = isset($_POST['new_window']) ? true : false;
        $model->status = isset($_POST['status']) ? 'active' : 'inactive';
        $model->type = $this->resource;

        if ($model->status != 'deleted') {
            $model->deleted_at = null;
            $model->deleted_by = null;
        }

        return $model;
    }

    /**
     * Sort action
     *
     * @param string $id The menu ID
     * @param string $dir The direction either up or down
     * @param int $sort The sort number
     * @return void
     * @throws ValidationException
     * @throws SaveException
     */
    public function sortAction($id, $dir, $sort)
    {
        $this->clearFormData();
        $this->secure($this->access);

        try {
            $sort = intval($sort);
            if ($sort < 0) {
                $sort *= -1;
            }

            if ($dir == 'up') {
                $query = 'UPDATE menu SET sort = sort + 1 WHERE sort=:sort AND id != :id';
            } else {
                $query = 'UPDATE menu SET sort = sort - 1 WHERE sort=:sort AND id != :id';
            }
            $this
                ->db
                ->query(
                    $query,
                    [
                        ':sort' => $sort,
                        ':id' => $id
                    ]
                );

            $this
                ->db
                ->query(
                    'UPDATE menu SET sort = :sort WHERE id = :id',
                    [
                        ':sort' => $sort,
                        ':id' => $id
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

    /**
     * Stats
     *
     * @return Phalcon\Mvc\Model\Resultset\Simple
     */
    public function stats()
    {
        $query = 'SELECT ';
        $query .= "(
            SELECT count(id) 
            FROM menu 
            WHERE status = 'active' AND type='" . $this->resource . "') AS active,";
        $query .= "(
            SELECT count(id) 
            FROM menu 
            WHERE status = 'inactive' AND type='" . $this->resource . "') AS inactive,";
        $query .= "(
            SELECT count(id) 
            FROM menu 
            WHERE deleted_at IS NOT NULL AND type='" . $this->resource . "') AS deleted,";

        $model = new Menu();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    /**
     * Update action
     *
     * @param string $id ID of the entry
     * @return void
     * @throws ValidationException
     * @throws SaveException
     */
    public function updateAction($id)
    {
        $this->secure($this->access);

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
            $this->validate();

            $model = $this->setData($model);

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the menu item',
                    $model->getMessages()
                );
            }

            (new MenuCategoriesController())->addCategories($model->id);

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

    /**
     * Validate
     *
     * @return void
     * @throws ValidationException
     * @throws RequestException
     */
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

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException(
                'Form validation failed, please double check the required fields',
                $messages
            );
        }
    }
}
