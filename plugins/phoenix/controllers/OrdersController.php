<?php

/**
 * Orders controller.
 *
 * @package     Kytschi\Phoenix\Controllers\OrdersController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Phoenix\Controllers\BasketController;
use Kytschi\Phoenix\Models\Products;
use Kytschi\Phoenix\Models\Orders;
use Kytschi\Phoenix\Models\OrderItems;
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
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class OrdersController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Notes;
    use Pagination;
    use Tags;

    public $access = [
        'super-user'
    ];

    public $global_url = '/orders';
    public $resource = 'order';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->sales . $this->global_url;
    }

    public function cloneAction()
    {
        $this->secure($this->access);

        $model = (new Orders())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $cloned = new Orders();
        foreach ($model as $key => $value) {
            $cloned->$key = $value;
        }

        $cloned->id = null;
        $cloned->number = self::getNumber();
        $cloned->created_by = null;
        $cloned->created_at = null;
        $cloned->updated_by = null;
        $cloned->updated_at = null;
        $cloned->deleted_by = null;
        $cloned->deleted_at = null;
        $cloned->status = 'active';

        if ($cloned->save() === false) {
            throw new SaveException(
                'Failed to clone the order',
                $cloned->getMessages()
            );
        }

        foreach ($model->items as $item) {
            if (empty($item->product)) {
                $this->saveFormWarning('One of the products have been deleted');
                continue;
            }

            $quantity = $item->quantity;
            if ($quantity > $item->product->stock) {
                $quantity = $item->product->stock;
                $this->saveFormWarning('One of the products does not have enough stock for that quantity');
            }

            $newItem = new OrderItems([
                'order_id' => $cloned->id,
                'product_id' => $item->product_id,
                'quantity' => $quantity
            ]);

            $newItem->sub_total = $newItem->quantity * $item->product->price;
            $newItem->vat = $newItem->sub_total * (!empty($item->product->vat) ? ($item->product->vat / 100) : 0);
            $newItem->total = $newItem->sub_total + $newItem->vat;

            if ($newItem->save() === false) {
                throw new SaveException(
                    'Failed to clone the order\'s product',
                    $newItem->getMessages()
                );
            }
        }

        $this->redirect(
            UrlHelper::backend(
                ($this->di->getConfig())->urls->sales . '/basket'
            )
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        $model = (new Orders())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        if (!self::isAdmin() && $model->updated_by != self::getUserId()) {
            throw new AuthorisationException('This order does not belong to you');
        }

        try {
            $model->softDelete();

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $this->saveFormDeleted('Order successfully marked as deleted');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    public function editAction()
    {
        $this->secure($this->access);

        $model = (new Orders())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle('Order view');

        return $this->view->partial(
            'phoenix/orders/edit',
            [
                'data' => $model
            ]
        );
    }

    public static function getNumber()
    {
        $model = (new Orders())->findFirst(['order' => 'number desc']);

        if (empty($model)) {
            return 1;
        }

        return ($model->number + 1);
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Orders');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'created_by',
                'status'
            ],
            'status'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Orders::class)
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
            'phoenix/orders/index',
            [
                'data' => $paginator->paginate(),
                'stats' => $this->stats()
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        $model = (new Orders())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        if (!self::isAdmin() && $model->updated_by != self::getUserId()) {
            throw new AuthorisationException('This order does not belong to you');
        }

        try {
            $model->recover();

            $this->addLog(
                $this->resource,
                $model->id,
                'warning',
                'Recovered by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Order successfully recovered');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    public function resumeAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        $model = (new Orders())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            if ($model->status == 'active') {
                throw new SaveException(
                    'Failed to resume the order as it is already being worked on'
                );
            } elseif ($model->status == 'deleted') {
                throw new SaveException(
                    'Failed to resume the order as it is in a deleted state'
                );
            }

            $model->status = 'active';

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to resume the order',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Resumed by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Order successfully resumed');
            $this->redirect(($this->di->getConfig())->urls->sales . '/products');
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    public function stats()
    {
        $model = new Orders();
        $table = $model->getSource();

        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE status = 'complete') AS complete,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'in progress') AS in_progress,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'inactive') AS inactive,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'deleted' AND deleted_at IS NOT NULL) AS deleted,";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public static function statusColour($status)
    {
        switch ($status) {
            case 'active':
                return 'badge-success';
                break;
            case 'inactive':
                return 'badge-warning';
                break;
            case 'deleted':
                return 'badge-danger';
                break;
            case 'dispatch':
                return 'badge-info';
                break;
            default:
                return 'badge-primary';
                break;
        }
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Orders())->findFirst([
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
            (new BasketController())->saveAddresses($model);

            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated(ucfirst(str_replace('-', ' ', $this->resource)) . ' has been updated');

            $this->clearFormData();
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
}
