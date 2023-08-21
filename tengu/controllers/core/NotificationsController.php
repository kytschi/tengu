<?php

/**
 * Logs controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\NotificationsController
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

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Notifications;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Pagination;
use Phalcon\Paginator\Adapter\QueryBuilder;

class NotificationsController extends ControllerBase
{
    use Form;
    use Logs;
    use Pagination;

    public $global_url = '/notifications';
    public $resource = 'notification';

    public function deleteAction()
    {
        $this->clearFormData();

        $model = (new Notifications())->findFirst([
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

            $this->saveFormUpdated('Notification has been marked as read');
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

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure();
        $this->setPageTitle('Notifications');

        $this->savePagination();

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Notifications::class)
            ->where('deleted_at IS NULL')
            ->orderBy('created_at DESC');

        if (!empty($this->search)) {
            $builder
                ->andWhere('summary LIKE :summary:')
                ->setBindParams(
                    [
                        'summary' => '%' . $this->search . '%'
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
            'core/notifications/index',
            [
                'data' => $paginator->paginate()
            ]
        );
    }
}
