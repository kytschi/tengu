<?php

/**
 * Spun export controller.
 *
 * @package     Kytschi\Makabe\Controllers\SpunExportController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Makabe\Controllers;

use Kytschi\Makabe\Exceptions\SpinException;
use Kytschi\Makabe\Models\Exports;
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

class SpunExportController extends ControllerBase
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
    public $resource = 'spun-export';
    public $type = 'spun-export';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Exports())->findFirst([
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

            $this->saveFormDeleted('Spun export content has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle("Exporting spun content");
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
                ->from(Exports::class)
                ->where('resource_id = :resource_id:')
                ->orderBy($this->orderBy . ' ' . $this->orderDir);

            $params = [
                'resource_id' => $this->dispatcher->getParam('id')
            ];

            $builder->andWhere('deleted_at IS NULL');

            if (!empty($this->search)) {
                $params = array_merge(
                    $params,
                    [
                        'name' => '%' . $this->search . '%'
                    ]
                );

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
                'makabe/spinner/export/index',
                [
                    'data' => $paginator->paginate(),
                    'page' => $page,
                    "spin" => $spin
                ]
            );
        } catch (\Exception $err) {
            throw new RequestException($err->getMessage());
        }
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

    public function startAction()
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
            'Kytschi\\Makabe\\Jobs\\SpunExportJob'
        );

        $this->addLog(
            $this->resource,
            $model->id,
            'warning',
            'Spun content export job created by ' . $this->getUserFullName()
        );

        $url = $this->global_url . '/' . $model->resource_id . '/spinner/' . $model->id . '/export';
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        $this->redirect(UrlHelper::backend($url));
    }
}
