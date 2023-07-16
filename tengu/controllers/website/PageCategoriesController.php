<?php

/**
 * Menu controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\PageCategoriesController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Website;

use Kytschi\Tengu\Controllers\Website\PagesController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\PageCategories;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Queue;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Tengu\Traits\Website\OldUrls;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class PageCategoriesController extends PagesController
{
    use Files;
    use Form;
    use Logs;
    use OldUrls;
    use Pagination;
    use Queue;
    use Tags;

    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url = '/page-categories';
    public $resource = 'page-category';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    public function addAction($template = '')
    {
        $this->secure($this->access);
        $this->setPageTitle('Add a category');

        if (!$template) {
            $this->setResource($this->dispatcher->getParam('type'));
        }

        return $this->view->partial(
            'website/page_categories/add',
            [
                'statuses' => $this->defaultStatuses(),
                'templates' => PagesController::getTemplates(),
                'type' => $this->resource
            ]
        );
    }

    public function addCategories($page_id, $user_id)
    {
        $this
            ->modelsManager
            ->executeQuery(
                'UPDATE ' .
                PageCategories::class .
                ' SET deleted_at=NOW(), deleted_by=:deleted_by: WHERE page_id = :page_id:',
                [
                    'deleted_by' => $user_id,
                    'page_id' => $page_id
                ]
            );

        if (empty($_POST['category_id'])) {
            return;
        }

        $table = (new PageCategories())->getSource();
        $params = [
            ':page_id' => $page_id,
            ':created_at' => date('Y-m-d H:i:s'),
            ':created_by' => $user_id,
            ':updated_at' => date('Y-m-d H:i:s'),
            ':updated_by' => $user_id
        ];

        $query = '';

        foreach ($_POST['category_id'] as $key => $id) {
            if (empty($id)) {
                continue;
            }

            $params = array_merge(
                $params,
                [
                    ':id_' . $key => (new Random())->uuid(),
                    ':category_id_' . $key => $id
                ]
            );

            $query .= '
                INSERT INTO ' . $table . ' (id, category_id, page_id, created_at, created_by, updated_at, updated_by)
                SELECT
                    :id_' . $key . ',
                    :category_id_' . $key . ',
                    :page_id,
                    :created_at,
                    :created_by,
                    :updated_at,
                    :updated_by
                FROM DUAL WHERE NOT EXISTS
                (
                    SELECT id, page_id, category_id, created_at, created_by, updated_at, updated_by
                    FROM ' . $table . '
                    WHERE page_id=:page_id AND category_id=:category_id_' . $key . ' 
                );
            UPDATE ' . $table . '
            SET deleted_at=NULL, deleted_by=NULL 
            WHERE page_id=:page_id AND category_id=:category_id_' . $key . ';';
        }
        
        if (empty($query)) {
            return;
        }

        $this->db->query(
            $query,
            $params
        );
    }

    public static function all($type = '')
    {
        $query = 'deleted_at IS NULL AND status = "active"';
        if ($type) {
            $query .= ' AND type="' . $type . '"';
        }
        
        return (new Pages())->find([
            'conditions' => $query,
            'order' => 'sort ASC'
        ]);
    }

    public function editAction($id, $template = '')
    {
        $this->secure($this->access);

        if (!$template) {
            $this->setResource($this->dispatcher->getParam('type'));
        }

        $this->setPageTitle('Edit the category');

        $model = (new Pages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        return $this->view->partial(
            'website/page_categories/edit',
            [
                'data' => $model,
                'statuses' => $this->defaultStatuses(),
                'templates' => PagesController::getTemplates()
            ]
        );
    }

    public function indexAction($template = '')
    {
        $this->clearFormData();
        $this->secure($this->access);
        $this->savePagination();
        $this->setFilters();

        if ($template) {
            $this->resource = $template;
            switch ($template) {
                case 'blog-posts':
                    $title = 'Blog post categories';
                    break;
                case 'portfolio':
                    $title = 'Portfolio categories';
                    break;
                default:
                    $title = 'Categories';
                    break;
            }

            $this->setResource($this->dispatcher->getParam('type'));
            $this->setPageTitle($title);
        }

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
            'website/page_categories/index',
            [
                'data' => $paginator->paginate(),
                'stats' => $this->stats()
            ]
        );
    }

    public function saveAction($ignore = false)
    {
        if (!$ignore || is_string($ignore)) {
            $this->setResource($this->dispatcher->getParam('type'));
        }

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

    private function setResource($type)
    {
        switch ($type) {
            case 'blog-posts':
                $this->resource = 'blog-post-category';
                $this->global_url = ($this->di->getConfig())->urls->cms . '/blog-posts/categories';
                break;
            case 'portfolio':
                $this->resource = 'portfolio-category';
                $this->global_url = ($this->di->getConfig())->urls->cms . '/portfolio/categories';
                break;
            default:
                $this->resource = 'page-category';
                break;
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
        if (!$ignore || is_string($ignore)) {
            $this->setResource($this->dispatcher->getParam('type'));
        }
        
        parent::updateAction($ignore);
    }
}
