<?php

/**
 * Keywords controller.
 *
 * @package     Kytschi\Makabe\Controllers\KeywordsController
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

namespace Kytschi\Makabe\Controllers;

use Kytschi\Makabe\Controllers\WordsController;
use Kytschi\Makabe\Models\Keywords;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Tengu\Traits\Website\Stats;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class KeywordsController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Notes;
    use Pagination;
    use Stats;
    use Tags;

    public $access = [
        'administrator',
        'super-user',
        'seo-manager'
    ];

    public $global_url = '/keywords';
    public $resource = 'keyword';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->mms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Adding a keyword');

        $found = false;

        if (!empty($_GET['keyword'])) {
            $model = (new Keywords())->findFirst([
                'conditions' => 'keyword = :keyword:',
                'bind' => [
                    'keyword' => $_GET['keyword']
                ]
            ]);
            if (!empty($model)) {
                $found = true;
                $url = $this->global_url . '/edit/' . $model->id;
                $this->redirect(UrlHelper::backend(rtrim($url, '/')));
            }
        }

        return $this->view->partial(
            'makabe/keywords/add',
            [
                'found' => $found
            ]
        );
    }

    public function addKeywordStat($model)
    {
        $keywords = (new Keywords())->find([
            'conditions' => 'deleted_at IS NULL'
        ]);

        if (empty($keywords)) {
            return;
        }

        foreach ($keywords as $keyword) {
            $content = '';
            if ($model->content) {
                $content = strip_tags($model->content);
            }

            if ($model->name) {
                $content .= ' ' . $model->nane;
            }

            if ($model->meta_keywords) {
                $content .= ' ' . $model->meta_keywords;
            }

            if ($model->meta_description) {
                $content .= ' ' . $model->meta_description;
            }

            if ($model->url) {
                $content .= ' ' . $model->url;
            }

            preg_match("/\b" . $keyword->keyword . "\b/i", $content, $matches);
            if ($matches) {
                $this->addStat(
                    $keyword->id,
                    $keyword->resource_type
                );
            }
        }
    }

    public static function count($keyword, $string, $case_sensitive)
    {
        if (empty($string)) {
            return 0;
        }

        return $case_sensitive ?
            substr_count($string, $keyword) : substr_count(strtolower($string), strtolower($keyword));
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Keywords())->findFirst([
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

            $this->saveFormDeleted('Keyword has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
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

        $model = (new Keywords())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle($model->keyword);

        return $this->view->partial(
            'makabe/keywords/edit',
            [
                'data' => $model
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Our keywords');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'keyword',
                'rank',
                'created_by'
            ],
            'keyword'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Keywords::class)
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'keyword' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('keyword LIKE :keyword:');
        }

        $deleted = 'deleted_at IS NULL';

        if (!empty($this->filters)) {
            foreach ($this->filters as $filter => $value) {
                if (empty($value)) {
                    continue;
                }

                switch ($filter) {
                    case 'status':
                        if ($value == 'deleted') {
                            $deleted = 'deleted_at IS NOT NULL';
                        }
                        break;
                }
            }
        }

        $builder->andWhere($deleted);
        $builder->setBindParams($params);

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        return $this->view->partial(
            'makabe/keywords/index',
            [
                'data' => $paginator->paginate()
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Keywords())->findFirst([
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

            $this->saveFormUpdated('Keyword has been recovered');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
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

            $model = $this->setData(new Keywords());

            $found = (new Keywords())->findFirst([
                'conditions' => 'keyword = :keyword:',
                'bind' => [
                    'keyword' => $model->keyword
                ]
            ]);

            if (!empty($found)) {
                $model = $found;

                if (!$model->deleted_at) {
                    throw new SaveException('Keyword already in the system');
                } else {
                    $model->restore();

                    $this->addLog(
                        $this->resource,
                        $model->id,
                        'warning',
                        'Recovered by ' . $this->getUserFullName()
                    );
                }
            } else {
                if ($model->save() === false) {
                    throw new SaveException(
                        'Failed to add the keyword',
                        $model->getMessages()
                    );
                }

                $this->addLog(
                    $this->resource,
                    $model->id,
                    'info',
                    'Created by ' . $this->getUserFullName()
                );
            }

            $this->addTagsFromRequest($model->id, true);

            $this->saveFormSaved('Keyword has been saved');
            $this->clearFormData();

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }
            $this->redirect(UrlHelper::backend($url));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);

            $url = $this->global_url . '/add';
            if (!empty($_GET['from'])) {
                $url .= '?' . urldecode($_GET['from']);
            }

            $this->redirect(UrlHelper::backend($url));
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
        $model->keyword = $_POST['keyword'];
        $model->campaign_id = !empty($_POST['campaign_id']) ? $_POST['campaign_id'] : null;

        $rank = (new WordsController())->countByWord($model->keyword);
        $model->rank = $rank ? $rank : 1;

        return $model;
    }

    public function stats()
    {
        $table = (new Keywords())->getSource();

        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE status = 'active') AS active,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'pending') AS pending,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'rejected') AS rejected,";
        $query .= "(SELECT count(id) FROM $table WHERE deleted_at IS NOT NULL) AS deleted,";

        $model = new Keywords();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public static function top()
    {
        return (new Keywords())->findFirst([
            'conditions' => 'deleted_at IS NULL',
            'order' => 'rank DESC'
        ]);
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Keywords())->findFirst([
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

            $found = (new Keywords())->findFirst([
                'conditions' => 'keyword = :keyword: AND id != :id:',
                'bind' => [
                    'keyword' => $model->keyword,
                    'id' => $model->id
                ]
            ]);

            if (!empty($found)) {
                throw new SaveException('Keyword already in the system');
            }

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the keyword',
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

            $this->saveFormUpdated('Keyword has been updated');

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
            'keyword',
            new PresenceOf(
                [
                    'message' => 'The keyword is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
