<?php

/**
 * Pages controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\PagesController
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

namespace Kytschi\Tengu\Controllers\Website;

use Kytschi\Makabe\Controllers\PersonasController;
use Kytschi\Makabe\Models\Campaigns;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Controllers\Website\PageCategoriesController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Files as ModelFile;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Website\PageFiles;
use Kytschi\Tengu\Models\Website\Templates;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Website\OldUrls;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Queue;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class PagesController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Notes;
    use OldUrls;
    use Pagination;
    use Queue;
    use Tags;

    public $access = [
        'administrator',
        'super-user',
        'content-writer'
    ];

    public $global_url = '/pages';
    public $global_add_url = '';
    public $global_category_url = '';

    public $resource = 'page';
    public $resource_category = 'page-category';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
        $this->global_add_url = $this->global_url . '/create';
        $this->global_category_url = $this->global_url . '/categories';
    }

    public function addAction($title = 'Build a page', $template = 'website/pages/add')
    {
        $this->secure($this->access);
        $this->setPageTitle($title);

        return $this->view->partial(
            $template,
            [
                'campaigns' => Campaigns::find(['conditions' => 'deleted_at IS NULL AND type="seo"']),
                'categories' => PageCategoriesController::all($this->resource_category),
                'parents' => Pages::find([
                    'conditions' => 'type=:type:',
                    'bind' => ['type' => $this->resource]
                ]),
                'statuses' => $this->defaultStatuses(),
                'templates' => self::getTemplates(),
                'url' => $this->global_url,
                'add_url' => $this->global_add_url,
                'resource' => $this->resource
            ]
        );
    }

    public static function all()
    {
        return (new Pages())->find(
            [
                'conditions' => 'deleted_at IS NULL AND status="active"',
                'order' => 'created_at DESC'
            ]
        );
    }

    public function checkURL($model)
    {
        $find = (new Pages())->findFirst([
            'conditions' => 'url = :url: AND id != :id:',
            'bind' => [
                'url' => $model->url,
                'id' => $model->id
            ]
        ]);

        if (!empty($find)) {
            throw new SaveException('URL already taken by another ' . str_replace('-', ' ', $find->type));
        }
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Pages())->findFirst([
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

            $this->saveFormDeleted('Page has been deleted');
            $this->redirect(
                UrlHelper::backend(
                    rtrim($url, '/')
                )
            );
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function editAction($id, $title = 'Managing the page', $template = 'website/pages/edit')
    {
        $this->secure($this->access);

        $model = (new Pages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle($title);

        return $this->view->partial(
            $template,
            [
                'campaigns' => Campaigns::find(['conditions' => 'deleted_at IS NULL AND type="seo"']),
                'categories' => PageCategoriesController::all($this->resource_category, $model->id),
                'data' => $model,
                'parents' => Pages::find([
                    'conditions' => 'type=:type: AND id!=:id:',
                    'bind' => [
                        'type' => $this->resource,
                        'id' => $model->id
                    ]
                ]),
                'statuses' => $this->defaultStatuses(),
                'templates' => self::getTemplates(),
                'url' => $this->global_url,
                'add_url' => $this->global_add_url,
                'category_url' => $this->global_category_url,
                'resource' => $this->resource
            ]
        );
    }

    public function get($limit = 0, $exclude = null, $random = false, $featured = false)
    {
        $query = 'type=:type: AND deleted_at IS NULL AND status="active"';
        $binds = ['type' => $this->resource];

        if ($exclude) {
            if (is_array($exclude)) {
                $query .= ' AND id NOT IN (';
                foreach ($exclude as $key => $item) {
                    $query .= ':exclude_' . $key . ':';
                    if ($key + 1 != count($exclude)) {
                        $query .= ', ';
                    }

                    $binds['exclude_' . $key] = $exclude;
                }
                $query .= ')';
            } elseif (is_string($exclude)) {
                $query .= ' AND id != :exclude:';
                $binds['exclude'] = $exclude;
            }
        }

        if ($featured) {
            $query .= ' AND feature=1';
        }

        $order = 'created_at DESC';
        if ($random) {
            $order = 'RAND()';
        }

        $params = [
            'conditions' => $query,
            'order' => $order
        ];

        if ($binds) {
            $params['bind'] = $binds;
        }

        if ($limit) {
            $params['limit'] = $limit;
        }

        return (new Pages())->find($params);
    }

    public function getById($id)
    {
        return (new Pages())->findFirst([
            'conditions' => 'id = :id: AND status="active" AND deleted_at IS NULL',
            'bind' => [
                'id' => $id
            ]
        ]);
    }

    public static function getTemplates()
    {
        return (new Templates())->find([
            'conditions' => 'deleted_at IS NULL',
            'order' => 'default DESC, name ASC'
        ]);
    }

    public function indexAction($title = 'Our pages', $template = 'website/pages/index')
    {
        $this->setPageTitle($title);

        $this->clearFormData();
        $this->secure($this->access);
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'name',
                'url',
                'created_by',
                'status'
            ],
            'name'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Pages::class)
            ->where('type = "' . $this->resource . '"')
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'name' => '%' . $this->search . '%',
                'url' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('name LIKE :name: OR url LIKE :url:');
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
            $template,
            [
                'url' => $this->global_url,
                'data' => $paginator->paginate(),
                'stats' => $this->stats()
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Pages())->findFirst([
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

            $this->saveFormUpdated('Page has been recovered');
            $this->redirect(
                UrlHelper::backend(rtrim($url, '/'))
            );
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function saveAction($ignore = false)
    {
        $this->secure($this->access);

        try {
            $this->validate();

            $model = $this->setData(new Pages());

            $this->checkURL($model);

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the ' . str_replace('-', ' ', $this->resource),
                    $model->getMessages()
                );
            }

            $this->addImage($model->id);
            $this->addCarouselImages($model->id, self::getUserId());
            $this->addTagsFromRequest($model->id, true);

            (new PageCategoriesController())->addCategories($model->id, self::getUserId());

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->addJobToQueue($this->resource, $model->id, 'Kytschi\Tengu\Jobs\PageSnapshot', 'low');

            $this->saveFormSaved(ucfirst(str_replace('-', ' ', $this->resource)) . ' has been saved');
            $this->clearFormData();

            if (method_exists($this, 'saveSubAction')) {
                $this->saveSubAction($model);
            }

            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_add_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function setData($model)
    {
        $model->template_id = $_POST['template_id'];
        $model->parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        $model->type = $this->resource;

        $model->url = '/' .
            trim(
                (
                    empty($_POST['url']) ?
                        $this->createSlug(strip_tags($_POST['name'])) :
                        UrlHelper::clean($_POST['url'])
                ),
                '/'
            );
        $model->canonical_url =
            !empty($_POST['canonical_url']) ?
                UrlHelper::clean($_POST['canonical_url']) :
                null;
        $model->summary = !empty($_POST['summary']) ? $_POST['summary'] : null;
        $model->status = isset($_POST['status']) ? 'active' : 'inactive';
        $model->searchable = isset($_POST['searchable']) ? true : false;
        $model->meta_description =
            !empty($_POST['meta_description']) ? strip_tags($_POST['meta_description']) : null;
        $model->meta_author = !empty($_POST['meta_author']) ? strip_tags($_POST['meta_author']) : null;
        $model->cover_image_id = !empty($_POST['cover_image']) ? $_POST['cover_image'] : null;
        $model->banner_image_id = !empty($_POST['banner_image']) ? $_POST['banner_image'] : null;
        $model->campaign_id = !empty($_POST['campaign_id']) ? $_POST['campaign_id'] : null;
        $model->search_tags = !empty($_POST['tags']) ? $this->searchTags($_POST['tags']) : null;
        $model->feature = !empty($_POST['feature']) ? true : false;
        $model->sitemap = !empty($_POST['sitemap']) ? true : false;
        $model->rating = !empty($_POST['rating']) ? intval($_POST['rating']) : 0;

        $keywords = '';
        if ($tags = json_decode($_POST['meta_keywords'])) {
            foreach ($tags as $tag) {
                $keywords .= $tag->value . ',';
            }
        }
        $model->meta_keywords = !empty($keywords) ? rtrim($keywords, ',') : null;

        $old_spinnable = $model->spinnable;
        $model->spinnable = isset($_POST['spinnable']) ? true : false;

        if ($old_spinnable && !$model->spinnable) {
            $model->content = $model->pre_spin_content;
            $model->name = $model->pre_spin_name;
            $model->last_spun = null;
            $model->current_spun_content->used_at = null;
            if ($model->current_spun_content->update() === false) {
                throw new SaveException(
                    'Failed to update the current spun content date',
                    $model->current_spun_content->getMessages()
                );
            }
        } elseif (!$model->spinnable) {
            $model->content = !empty($_POST['content']) ? $_POST['content'] : null;
            $model->name = strip_tags($_POST['name']);
            $model->pre_spin_content = $model->content;
            $model->pre_spin_name = $model->name;
        }

        if ($model->status != 'deleted') {
            $model->deleted_at = null;
            $model->deleted_by = null;
        }

        $model->slogan = !empty($_POST['slogan']) ? $_POST['slogan'] : null;

        return $model;
    }

    public function stats()
    {
        $query = 'SELECT ';
        $query .= "(
            SELECT count(id)
            FROM pages
            WHERE type = '" . $this->resource . "' AND status = 'active'
        ) AS active,";
        $query .= "(
            SELECT count(id) 
            FROM pages 
            WHERE type = '" . $this->resource . "' AND status = 'pending'
        ) AS pending,";
        $query .= "(
            SELECT count(id) 
            FROM pages 
            WHERE type = '" . $this->resource . "' AND status = 'inactive'
        ) AS inactive,";
        $query .= "(
            SELECT count(id) 
            FROM pages 
            WHERE type = '" . $this->resource . "' AND status = 'rejected'
        ) AS rejected,";
        $query .= "(
            SELECT count(id) 
            FROM pages 
            WHERE type = '" . $this->resource . "' AND deleted_at IS NOT NULL
        ) AS deleted,";

        $model = new Pages();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function updateAction($ignore = false)
    {
        $this->secure($this->access);

        $model = (new Pages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $url = UrlHelper::backend($this->global_url . '/edit/' . $model->id);
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        try {
            $this->validate();

            $model = $this->setData($model);
            $this->checkURL($model);

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the ' . str_replace('-', ' ', $this->resource),
                    $model->getMessages()
                );
            }

            $this->addImage($model->id);
            $this->addCarouselImages($model->id, self::getUserId());
            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);
            $this->addOldUrl($model->id);

            (new PageCategoriesController())->addCategories($model->id, self::getUserId());

            if (($this->di->getConfig())->apps->makabe) {
                (new PersonasController())->attachPagePersona($model->id);
            }

            if (!empty($_POST['keyword_note'])) {
                $this->addKeywordNotes($this->resource);
            }

            if (!empty($_POST['media_label']) && !empty($_POST['media_label_id'])) {
                if (
                    $file = ModelFile::findFirst(
                        [
                            'conditions' => 'id = :id:',
                            'bind' => ['id' => $_POST['media_label_id']]
                        ]
                    )
                ) {
                    $file->label = $_POST['media_label'];
                    if ($file->update() === false) {
                        throw new SaveException(
                            'Failed to update the media item',
                            $file->getMessages()
                        );
                    }

                    if (!empty($_POST['media_tags'])) {
                        $this->addTags(
                            json_decode($_POST['media_tags']),
                            $file->id,
                            true,
                            'file'
                        );
                    }
                }
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->addJobToQueue($this->resource, $model->id, 'Kytschi\Tengu\Jobs\PageSnapshot', 'low');

            if (!empty($_FILES['image']['name'])) {
                $url = UrlHelper::append($url, 'tab=media-tab');
            }

            $this->saveFormUpdated(ucfirst(str_replace('-', ' ', $this->resource)) . ' has been updated');
            $this->clearFormData();

            if (method_exists($this, 'updateSubAction')) {
                $this->updateSubAction($model);
            }

            $this->redirect($url);
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

        $validation->add(
            'template_id',
            new PresenceOf(
                [
                    'message' => 'The template is required',
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
