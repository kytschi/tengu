<?php

/**
 * Spin controller.
 *
 * @package     Kytschi\Makabe\Controllers\SpinController
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

namespace Kytschi\Makabe\Controllers;

use Kytschi\Makabe\Exceptions\SpinException;
use Kytschi\Makabe\Models\Campaigns;
use Kytschi\Makabe\Models\ContentBackup;
use Kytschi\Makabe\Models\SpinContent;
use Kytschi\Makabe\Models\SpinContentKeywords;
use Kytschi\Makabe\Models\SpunContent;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Queue;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Encryption\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class SpinController extends ControllerBase
{
    use Form;
    use Logs;
    use Pagination;
    use Queue;

    public $access = [
        'administrator',
        'super-user',
        'content-writer'
    ];

    public $global_url = '/pages';
    public $resource = 'spin-content';
    public $type = 'pages';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);

        $this->setPageTitle('Spin content');

        return $this->view->partial(
            'makabe/spinner/add',
            [
                'campaigns' => Campaigns::find(['conditions' => 'deleted_at IS NULL AND type="seo"']),
                'page_id' => $this->dispatcher->getParam('id'),
                'page_type' => $this->dispatcher->getParam('type')
            ]
        );
    }

    public function cleanAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        $model = (new SpinContent())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setUrl($this->dispatcher->getParam('type'), $this->dispatcher->getParam('parent'));

        try {
            $paragraphs = preg_split('/\n+/', strip_tags($model->content));
            $content = '';
            foreach ($paragraphs as $text) {
                if (empty($text)) {
                    continue;
                }
                $content .= '<p>' . $text . '</p>';
            }
            
            $model->content = $content;
            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the content',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Spin content cleaned by ' . $this->getUserFullName()
            );

            $this->addLog(
                $this->resource,
                $model->resource_id,
                'info',
                'Spin content cleaned by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/' . $model->resource_id . '/spinner/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Spin content has been cleaned');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    private function convert($size)
    {
        $unit = array('b','kb','mb','gb','tb','pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

    private function predictedSpins($model)
    {
        $spins = 1;

        $vars = ['content', 'name', 'url', 'meta_keywords', 'meta_description'];

        foreach ($vars as $var) {
            if (empty($model->$var)) {
                continue;
            }
            
            preg_match_all("/{+(.*?)}/", $model->$var, $content_words);
            if (!empty($content_words)) {
                foreach ($content_words[1] as $key => $words) {
                    $splits = explode('|', $words);
                    $spins *= count($splits);
                }
            }
        }
        
        return $spins;
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new SpinContent())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setUrl($this->dispatcher->getParam('type'), $this->dispatcher->getParam('parent'));

        try {
            $model->softDelete();

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Spin content deleted by ' . $this->getUserFullName()
            );

            $this->addLog(
                $this->resource,
                $model->resource_id,
                'danger',
                'Spin content deleted by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/' . $model->resource_id . '/spinner/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Spin content has been deleted');
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

        $model = (new SpinContent())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle('Spin content');

        return $this->view->partial(
            'makabe/spinner/edit',
            [
                'data' => $model,
                'campaigns' => Campaigns::find(['conditions' => 'deleted_at IS NULL AND type="seo"']),
                'predicted_spins' => $this->predictedSpins($model)
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle("Content spinner");
        $this->savePagination();
        $this->setFilters();

        $this->setUrl($this->dispatcher->getParam('type'), $this->dispatcher->getParam('parent'));

        $this->setIndexDefaults(
            [
                'label',
                'created_at',
            ],
            'label'
        );

        $page = (new Pages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($page)) {
            return $this->notFound();
        }

        try {
            $builder = $this
                ->modelsManager
                ->createBuilder()
                ->from(SpinContent::class)
                ->where('resource_id = :resource_id:')
                ->orderBy($this->orderBy . ' ' . $this->orderDir);

            $params = [
                'resource_id' => $this->dispatcher->getParam('id')
            ];

            if (!empty($this->search)) {
                $params = array_merge(
                    $params,
                    [
                        'label' => '%' . $this->search . '%',
                        'content' => '%' . $this->search . '%'
                    ]
                );

                $builder->andWhere('label LIKE :label: OR content LIKE :content:');
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
                'makabe/spinner/index',
                [
                    'data' => $paginator->paginate(),
                    'page' => $page,
                    'stats' => $this->stats($page->id)
                ]
            );
        } catch (\Exception $err) {
            throw new RequestException($err->getMessage());
        }
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new SpinContent())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setUrl($this->dispatcher->getParam('type'), $this->dispatcher->getParam('parent'));

        try {
            $model->recover();

            $this->addLog(
                $this->resource,
                $model->id,
                'warning',
                'Spin content recovered by ' . $this->getUserFullName()
            );

            $this->addLog(
                $this->resource,
                $model->resource_id,
                'warning',
                'Spin content recovered by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/' . $model->resource_id . '/spinner/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Spin content has been recovered');
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

        $this->setUrl($this->dispatcher->getParam('type'));

        $page = (new Pages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($page)) {
            return $this->notFound();
        }

        $this->setUrl($this->dispatcher->getParam('type'), $this->dispatcher->getParam('parent'));
        $url = $this->global_url . '/' . $page->id . '/spinner/add';
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        try {
            $this->validate();

            $model = $this->setData(new SpinContent());
            $model->resource_id = $page->id;
            $model->resource = $this->type;

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to save the spin content',
                    $model->getMessages()
                );
            }

            $this->saveKeywords($model);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved('Spin content has been saved');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
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

    public function saveKeywords($model)
    {
        $this
            ->modelsManager
            ->executeQuery(
                'UPDATE ' .
                SpinContentKeywords::class .
                ' SET deleted_at=NOW(), deleted_by=:deleted_by: 
                WHERE spin_content_id = :spin_content_id:',
                [
                    'deleted_by' => self::getUserId(),
                    'spin_content_id' => $model->id
                ]
            );

        if (empty($_POST['keywords'])) {
            return;
        }

        $table = (new SpinContentKeywords())->getSource();
        $query = '';

        $params = [
            ':spin_content_id' => $model->id,
            ':created_at' => date('Y-m-d H:i:s'),
            ':created_by' => self::getUserId(),
            ':updated_at' => date('Y-m-d H:i:s'),
            ':updated_by' => self::getUserId()
        ];

        foreach ($_POST['keywords'] as $key => $id) {
            $query .=
                'INSERT INTO ' . $table . '
                    (
                        id,
                        spin_content_id,
                        keyword_id,
                        created_at,
                        created_by,
                        updated_at,
                        updated_by
                    )

                    SELECT
                        :id_' . $key . ',
                        :spin_content_id,
                        :keyword_id_' . $key . ',
                        :created_at,
                        :created_by,
                        :updated_at,
                        :updated_by
                    FROM DUAL
                    WHERE NOT EXISTS
                    (
                        SELECT
                            id,
                            spin_content_id,
                            keyword_id,
                            created_at,
                            created_by,
                            updated_at,
                            updated_by
                        FROM ' . $table . '
                        WHERE
                            spin_content_id=:spin_content_id AND
                            keyword_id=:keyword_id_' . $key . '
                    );

                UPDATE ' . $table . ' SET deleted_at=NULL, deleted_by=NULL
                WHERE
                    spin_content_id=:spin_content_id AND
                    keyword_id=:keyword_id_' . $key . ';';

                $params = array_merge(
                    $params,
                    [
                        ':id_' . $key => (new Random())->uuid(),
                        ':keyword_id_' . $key => $id
                    ]
                );
        }

        $this->db->query($query, $params);
    }

    public function setData($model)
    {
        $model->label = $_POST['label'];
        $model->content = $_POST['content'];
        $model->name = !empty($_POST['name']) ? $_POST['name'] : null;
        $model->url = !empty($_POST['url']) ? $_POST['url'] : null;
        $model->campaign_id = !empty($_POST['campaign_id']) ? $_POST['campaign_id'] : null;
        //$model->canonical_url = !empty($_POST['canonical_url']) ? $_POST['canonical_url'] : null;
        $model->meta_keywords = !empty($_POST['meta_keywords']) ? $_POST['meta_keywords'] : null;
        $model->meta_description = !empty($_POST['meta_description']) ? $_POST['meta_description'] : null;

        return $model;
    }

    private function setURL($type, $parent = '')
    {
        switch ($type) {
            case 'blog-posts':
                $this->global_url = ($this->di->getConfig())->urls->cms . '/blog-posts';
                $this->type = 'blog-post';
                break;
            case 'categories':
                $this->global_url = ($this->di->getConfig())->urls->cms . '/' . $parent . '/categories';
                $this->type = 'blog-post-category';
                break;
            case 'portfolio':
                $this->global_url = ($this->di->getConfig())->urls->cms . '/portfolio';
                $this->type = 'portfolio';
                break;
            default:
                $this->global_url = ($this->di->getConfig())->urls->cms . '/pages';
                $this->type = 'page';
                break;
        }
    }

    public function spinContentAction()
    {
        $this->secure($this->access);

        $this->setUrl($this->dispatcher->getParam('type'), $this->dispatcher->getParam('parent'));

        $model = (new SpinContent())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->addJobToQueue(
            $this->resource,
            $model->id,
            'Kytschi\\Makabe\\Jobs\\SpinJob',
            'high'
        );

        $this->addLog(
            $this->resource,
            $model->id,
            'warning',
            'Spin content job created by ' . $this->getUserFullName()
        );

        $url = $this->global_url . '/' . $model->resource_id . '/spinner/edit/' . $model->id;
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        $this->redirect(UrlHelper::backend($url));
    }

    public function stats($id)
    {
        $table = (new SpunContent())->getSource();
        $query = 'SELECT ';
        $query .= "
            (
                SELECT count(id) FROM $table 
                WHERE deleted_at IS NULL AND status='pending' AND resource_id='" . $id . "'
            ) AS pending,";
        $query .= "
            (
                SELECT count(id) FROM $table 
                WHERE deleted_at IS NOT NULL AND resource_id='" . $id . "'
            ) AS deleted";

        $model = new SpunContent();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $this->setUrl($this->dispatcher->getParam('type'), $this->dispatcher->getParam('parent'));

        $model = (new SpinContent())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $url = $this->global_url . '/' . $model->resource_id . '/spinner/edit/' . $model->id;
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        try {
            $this->validate();

            $model = $this->setData($model);
            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the content',
                    $model->getMessages()
                );
            }

            $this->saveKeywords($model);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            if (!empty($_POST['collapse'])) {
                $url = UrlHelper::append($url, ['collapse' => $_POST['collapse']]);
            }

            $this->saveFormUpdated('Spin content has been updated');
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

    public function validate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            'content',
            new PresenceOf(
                [
                    'message' => 'The content is required',
                ]
            )
        );

        $validation->add(
            'label',
            new PresenceOf(
                [
                    'message' => 'The label is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
