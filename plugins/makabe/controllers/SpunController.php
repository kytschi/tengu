<?php

/**
 * Spun controller.
 *
 * @package     Kytschi\Makabe\Controllers\SpunController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Makabe\Controllers;

use Kytschi\Makabe\Exceptions\SpinException;
use Kytschi\Makabe\Models\SpinContent;
use Kytschi\Makabe\Models\SpunContent;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
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

class SpunController extends ControllerBase
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
    public $resource = 'spun-content';
    public $type = 'pages';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new SpunContent())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setUrl($this->dispatcher->getParam('type'));

        try {
            $model->softDelete();

            $this->addLog(
                $model->page->type,
                $model->page_id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/' . $model->page_id . '/spinner/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Spun content has been deleted');
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

        $model = (new SpunContent())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle('Spun content');

        return $this->view->partial(
            'makabe/spinner/spun/edit',
            [
                'data' => $model
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle("Content spun");
        $this->savePagination();
        $this->setFilters();

        $this->setUrl($this->dispatcher->getParam('type'));

        $this->setIndexDefaults(
            [
                'name',
                'created_at',
            ],
            'name'
        );

        $page = (new Pages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('page_id')
            ]
        ]);

        if (empty($page)) {
            return $this->notFound();
        }

        $spin = (new SpinContent())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($spin)) {
            return $this->notFound();
        }

        try {
            $builder = $this
                ->modelsManager
                ->createBuilder()
                ->from(SpunContent::class)
                ->where('spin_content_id = :spin_content_id:')
                ->orderBy($this->orderBy . ' ' . $this->orderDir);

            $params = [
                'spin_content_id' => $this->dispatcher->getParam('id')
            ];

            $builder->andWhere('deleted_at IS NULL');

            if (!empty($this->search)) {
                $params = array_merge(
                    $params,
                    [
                        'name' => '%' . $this->search . '%',
                        'content' => '%' . $this->search . '%'
                    ]
                );

                $builder->andWhere('name LIKE :name: OR content LIKE :content:');
            }

            if (!empty($this->filters)) {
                $iLoop = 1;
                foreach ($this->filters as $filter => $value) {
                    if (empty($value)) {
                        continue;
                    }
    
                    switch ($filter) {
                        case 'used':
                            $builder->andWhere('used_at IS NOT NULL');
                            break;
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
                'makabe/spinner/spun/index',
                [
                    'data' => $paginator->paginate(),
                    'page' => $page,
                    "spin" => $spin,
                    'stats' => $this->stats($spin->id)
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

        $model = (new SpunContent())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setUrl($this->dispatcher->getParam('type'));

        try {
            $model->recover();

            $this->addLog(
                $this->resource,
                $model->id,
                'warning',
                'Recovered by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/' . $model->page_id . '/spinner/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Spun content has been recovered');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function setData($model)
    {
        $model->label = $_POST['label'];
        $model->content = $_POST['content'];
        $model->name = !empty($_POST['name']) ? $_POST['name'] : null;
        $model->url = !empty($_POST['url']) ? $_POST['url'] : null;
        $model->canonical_url = !empty($_POST['canonical_url']) ? $_POST['canonical_url'] : null;
        $model->meta_keywords = !empty($_POST['meta_keywords']) ? $_POST['meta_keywords'] : null;
        $model->meta_description = !empty($_POST['meta_description']) ? $_POST['meta_description'] : null;

        return $model;
    }

    private function setURL($type)
    {
        switch ($type) {
            case 'blog-posts':
                $this->global_url = ($this->di->getConfig())->urls->cms . '/blog-posts';
                $this->type = 'blog-post';
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

    public function stats($id = null)
    {
        $extra = ' AND spin_content_id="' . $id . '"';
        $table = (new SpunContent())->getSource();
        $query = 'SELECT ';
        $query .= "(SELECT COUNT(id) FROM $table WHERE status='live' AND deleted_at IS NULL" . $extra . ") AS live,";
        $query .= "(SELECT COUNT(id) FROM $table WHERE status='pending' AND deleted_at IS NULL" . $extra . ") AS pending,";
        $query .= "(SELECT COUNT(id) FROM $table WHERE deleted_at IS NOT NULL" . $extra . ") AS deleted,";
        $query .= "(SELECT COUNT(id) FROM $table WHERE used_at IS NOT NULL" . $extra . ") AS used";

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

        $this->setUrl($this->dispatcher->getParam('type'));

        $model = (new SpunContent())->findFirst([
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

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Spun content has been updated');
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
