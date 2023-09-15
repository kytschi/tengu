<?php

/**
 * Page categories controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\PageCategoriesController
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

use Kytschi\Tengu\Controllers\Website\PagesController;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\PageCategories;
use Kytschi\Tengu\Models\Website\Pages;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Encryption\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class PageCategoriesController extends PagesController
{
    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url = '/pages/categories';
    public $global_add_url = '';
    public $global_from_url = '/pages';

    public $resource = 'page-category';
    public $category_support = false;

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
        $this->global_from_url = ($this->di->getConfig())->urls->cms . $this->global_from_url;
        $this->global_add_url = $this->global_url . '/add';
        $this->global_category_url = $this->global_url;
    }

    public function addAction($title = 'Add a category', $template = 'website/pages/add')
    {
        parent::addAction($title, $template);
    }

    public function addCategories($page_id, $user_id)
    {
        $this
            ->modelsManager
            ->executeQuery(
                'UPDATE ' .
                PageCategories::class .
                ' SET deleted_at=NOW(), deleted_by=:deleted_by:, primary=0 WHERE page_id = :page_id:',
                [
                    'deleted_by' => $user_id,
                    'page_id' => $page_id
                ]
            );

        $primary = '';
        if (!empty($_POST['category_primary'])) {
            $primary = $_POST['category_primary'];
        }

        if (!empty($_POST['category_id'])) {
            $table = (new PageCategories())->getSource();

            foreach ($_POST['category_id'] as $key => $id) {
                if (empty($id)) {
                    continue;
                }
                $model = PageCategories::findFirst([
                    'conditions' => 'category_id=:category_id: AND page_id=:page_id:',
                    'bind' => [
                        'page_id' => $page_id,
                        'category_id' => $id
                    ]
                ]);

                if (empty($model)) {
                    $model = new PageCategories([
                        'category_id' => $id,
                        'page_id' => $page_id,
                        'primary' => ($primary == $id ? true : false)
                    ]);

                    if ($model->save() === false) {
                        throw new SaveException(
                            'Failed to attach the item to the category',
                            $model->getMessages()
                        );
                    }
                } else {
                    $model->deleted_at = null;
                    $model->deleted_by = null;
                    $model->primary = ($primary == $id ? true : false);

                    if ($model->update() === false) {
                        throw new SaveException(
                            'Failed to attach the item to the category',
                            $model->getMessages()
                        );
                    }
                }
            }
        }

        if (!empty($_POST['category_sort'])) {
            $iLoop = 0;
            foreach ($_POST['category_sort'] as $id => $sort) {
                if (empty($id)) {
                    continue;
                }

                $this->db->query(
                    'UPDATE ' . $table . '
                    SET sort=:sort 
                    WHERE page_id=:page_id_' . $iLoop . ' AND category_id=:category_id',
                    [
                        ':category_id' => $page_id,
                        ':page_id_' . $iLoop => $id,
                        ':sort' => $sort
                    ]
                );
                $iLoop++;
            }
        }
    }

    public static function all($type = null, $exclude = null)
    {
        $binds = [];
        $query = 'deleted_at IS NULL';
        if ($type) {
            $query .= ' AND type=:type:';
            $binds['type'] = $type;
        } else {
            $query .= ' AND type IN (
                "event-category",
                "page-category",
                "blog-post-category",
                "portfolio-category",
                "product-category",
                "product"
            )';
        }

        if ($exclude) {
            $query .= ' AND id!=:id:';
            $binds['id'] = $exclude;
        }

        return (new Pages())->find([
            'conditions' => $query,
            'bind' => $binds,
            'order' => 'name ASC'
        ]);
    }

    public function editAction($id, $title = 'Edit the category', $template = 'website/pages/edit')
    {
        parent::editAction($id, $title, $template);
    }

    public function indexAction($title = 'Our categories', $template = 'website/page_categories/index')
    {
        $this->clearFormData();
        $this->secure($this->access);
        $this->savePagination();
        $this->setFilters();

        $this->setPageTitle(str_replace('-', ' ', strtolower($this->resource)) . ' categories');

        $params = [
            'type' => $this->resource
        ];

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Pages::class)
            ->where('type = :type:')
            ->orderBy('sort ASC');

        if (!empty($this->search)) {
            $params['name'] = '%' . $this->search . '%';
            $builder->andWhere('name LIKE :name:');
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
            $template,
            [
                'data' => $paginator->paginate(),
                'stats' => $this->stats()
            ]
        );
    }

    public function saveAction($ignore = false)
    {
        parent::saveAction($ignore);
    }

    public function saveSubAction($model)
    {
        $page = new Pages();
        $table = $page->getSource();
        $sort = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $page,
            $page->getReadConnection()->query(
                'SELECT count(id) AS sort FROM ' . $table . ' WHERE type="' . $this->resource . '"'
            )
        ))->toArray()[0]['sort'];

        $model->sort = $sort;
        if ($model->update() === false) {
            throw new SaveException(
                'Failed to update the category',
                $model->getMessages()
            );
        }
    }

    public function sortAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        try {
            $table = (new Categories())->getSource();

            $sort = intval($this->dispatcher->getParam('sort'));
            if ($sort < 0) {
                $sort *= -1;
            }

            $dir = $this->dispatcher->getParam('dir');
            if ($dir == 'up') {
                $query = 'UPDATE ' . $table . ' SET sort = sort + 1 WHERE sort=:sort AND id != :id';
            } else {
                $query = 'UPDATE ' . $table . ' SET sort = sort - 1 WHERE sort=:sort AND id != :id';
            }

            $query .= '; UPDATE ' . $table . ' SET sort = :sort WHERE id = :id';

            $this
                ->db
                ->query(
                    $query,
                    [
                        ':sort' => $sort,
                        ':id' => $this->dispatcher->getParam('id')
                    ]
                );

            $this->saveFormSaved('Categories have been sorted');
            $this->lastUpdate();
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

    public function updateAction($ignore = false)
    {
        parent::updateAction($ignore);
    }
}
