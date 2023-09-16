<?php

/**
 * Communications controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\CommunicationsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Communications;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Pagination;
use Phalcon\Paginator\Adapter\NativeArray;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class CommunicationsController extends ControllerBase
{
    use Form;
    use Logs;
    use Pagination;

    public $global_url = '/messages';
    public $resource = 'message';

    public static function count()
    {
        return (new Communications())
            ->find(['conditions' => 'deleted_at IS NULL'])
            ->count();
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure();

        $model = (new Communications())->findFirst([
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

            $url = $this->global_url;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }
            
            $this->saveFormDeleted('Message has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function inboxAction()
    {
        $this->clearFormData();

        $this->secure();

        $this->savePagination();
        $this->setFilters();

        $this->setPageTitle('Inbox');

        $this->setIndexDefaults(
            [
                'from_name',
                'message',
                'created_at'
            ],
            'created_at'
        );

        if (empty($this->search)) {
            $builder = $this
                ->modelsManager
                ->createBuilder()
                ->from(Communications::class)
                ->where('box="inbox" AND deleted_at IS NULL')
                ->orderBy($this->orderBy . ' ' . $this->orderDir);

            $paginator = new QueryBuilder(
                [
                    'builder' => $builder,
                    'limit' => $this->perPage,
                    'page' => $this->page,
                ]
            );
        } else {
            $results = Communications::find(
                [
                    'conditions' => 'box="inbox" AND deleted_at IS NULL'
                ]
            );

            $data = [];

            foreach ($results as $result) {
                if (
                    str_contains(StringHelper::decrypt($result->subject), $this->search) ||
                    str_contains(StringHelper::decrypt($result->from_name), $this->search) ||
                    str_contains(StringHelper::decrypt($result->from_email), $this->search)
                ) {
                    $data[] = $result;
                }
            }

            $paginator = new NativeArray(
                [
                    "data" => $data,
                    'limit' => $this->perPage,
                    'page' => $this->page
                ]
            );
        }

        return $this->view->partial(
            'core/messages/inbox',
            [
                'data' => $paginator->paginate()
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure();

        $model = (new Communications())->findFirst([
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

            $url = $this->global_url;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'warning',
                'Recovered by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated("You've recovered the message");
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function sentAction()
    {
        $this->clearFormData();

        $this->secure();

        $this->savePagination();
        $this->setFilters();

        $this->setPageTitle('Sent');

        $this->setIndexDefaults(
            [
                'from_name',
                'message',
                'created_at'
            ],
            'created_at'
        );

        if (empty($this->search)) {
            $builder = $this
                ->modelsManager
                ->createBuilder()
                ->from(Communications::class)
                ->where('box="sent" AND deleted_at IS NULL')
                ->orderBy($this->orderBy . ' ' . $this->orderDir);

            $paginator = new QueryBuilder(
                [
                    'builder' => $builder,
                    'limit' => $this->perPage,
                    'page' => $this->page,
                ]
            );
        } else {
            $results = Communications::find(
                [
                    'conditions' => 'box="sent" AND deleted_at IS NULL'
                ]
            );

            $data = [];

            foreach ($results as $result) {
                if (
                    str_contains(StringHelper::decrypt($result->subject), $this->search) ||
                    str_contains(StringHelper::decrypt($result->from_name), $this->search) ||
                    str_contains(StringHelper::decrypt($result->from_email), $this->search)
                ) {
                    $data[] = $result;
                }
            }

            $paginator = new NativeArray(
                [
                    "data" => $data,
                    'limit' => $this->perPage,
                    'page' => $this->page
                ]
            );
        }

        return $this->view->partial(
            'core/messages/sent',
            [
                'data' => $paginator->paginate()
            ]
        );
    }

    public function trashAction()
    {
        $this->clearFormData();

        $this->secure();

        $this->savePagination();
        $this->setFilters();

        $this->setPageTitle('Trash');

        $this->setIndexDefaults(
            [
                'from_name',
                'message',
                'created_at'
            ],
            'created_at'
        );

        if (empty($this->search)) {
            $builder = $this
                ->modelsManager
                ->createBuilder()
                ->from(Communications::class)
                ->where('deleted_at IS NOT NULL')
                ->orderBy($this->orderBy . ' ' . $this->orderDir);

            $paginator = new QueryBuilder(
                [
                    'builder' => $builder,
                    'limit' => $this->perPage,
                    'page' => $this->page,
                ]
            );
        } else {
            $results = Communications::find(
                [
                    'conditions' => 'deleted_at IS NOT NULL'
                ]
            );

            $data = [];

            foreach ($results as $result) {
                if (
                    str_contains(StringHelper::decrypt($result->subject), $this->search) ||
                    str_contains(StringHelper::decrypt($result->from_name), $this->search) ||
                    str_contains(StringHelper::decrypt($result->from_email), $this->search)
                ) {
                    $data[] = $result;
                }
            }

            $paginator = new NativeArray(
                [
                    "data" => $data,
                    'limit' => $this->perPage,
                    'page' => $this->page
                ]
            );
        }

        return $this->view->partial(
            'core/messages/trash',
            [
                'data' => $paginator->paginate()
            ]
        );
    }
}
