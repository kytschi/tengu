<?php

/**
 * Dispatch controller.
 *
 * @package     Kytschi\Phoenix\Controllers\DispatchController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Phoenix\Controllers\OrdersController;
use Kytschi\Phoenix\Controllers\ShippingCompaniesController;
use Kytschi\Phoenix\Models\OrderAddresses;
use Kytschi\Phoenix\Models\Orders;
use Kytschi\Phoenix\Models\OrderItems;
use Kytschi\Phoenix\Models\OrderShipping;
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
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\Digit;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class DispatchController extends ControllerBase
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

    public $global_url = '/dispatch';
    public $resource = 'order';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->sales . $this->global_url;
    }

    public static function count()
    {
        $model = new Orders();
        $table = $model->getSource();

        $query = "SELECT count(id) AS total FROM $table WHERE status = 'dispatch'";
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0]['total'];
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

    public function flagAction()
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

        $url = $this->global_url;
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        try {
            $this->validateFlag();
            $this->saveFormUpdated('Shipping has been booked for the order');
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

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Dispatching orders');
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
            ->where('id != ""')
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
                        $builder->andWhere('status = :status_' . $iLoop . ':');
                        $params['status_' . $iLoop] = $value;
                        $iLoop++;
                        break;
                }
            }
        } else {
            $builder->andWhere('status="dispatch"');
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
            'phoenix/dispatch/index',
            [
                'data' => $paginator->paginate(),
                'shipping_companies' => (new ShippingCompaniesController())->get(),
                'stats' => $this->stats()
            ]
        );
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

    public function shippingAction()
    {
        $this->secure($this->access);

        if (empty($_POST['id'])) {
            throw new ValidationException('Validation failed, missing order ID');
        }

        $model = (new Orders())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $_POST['id']
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $url = $this->global_url;
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        try {
            $amount = $model->total;
            $quantity = $model->quantity;

            if (!empty($_POST['quantity'])) {
                $clone = false;
                $quantity = 0;

                foreach ($model->items as $item) {
                    if (!empty($_POST['quantity'][$item->id])) {
                        $item->fulfilled = intval($_POST['quantity'][$item->id]);
                        $quantity += $item->fulfilled;
                        if (($item->quantity - $item->fulfilled) > 0) {
                            $clone = true;
                        }

                        if ($item->update() === false) {
                            throw new SaveException(
                                'Failed to update order item',
                                $item->getMessages()
                            );
                        }
                    }
                }

                if ($clone) {
                    $cloned = $model->clone([
                        'status' => 'dispatch',
                        'number' => (new OrdersController())::getNumber()
                    ]);

                    if ($cloned->save() === false) {
                        throw new SaveException(
                            'Failed to create the order',
                            $cloned->getMessages()
                        );
                    }

                    foreach ($model->items as $key => $item) {
                        if (($item->quantity - $item->fulfilled) > 0) {
                            $clonedItem = $item->clone([
                                'order_id' => $cloned->id,
                                'quantity' => ($item->quantity - $item->fulfilled)
                            ]);

                            $clonedItem->sub_total = $clonedItem->quantity * $item->product->price;
                            $clonedItem->vat = $clonedItem->sub_total *
                                (!empty($item->product->vat) ? ($item->product->vat / 100) : 0);
                            $clonedItem->total = $clonedItem->sub_total + $clonedItem->vat;

                            if ($clonedItem->save() === false) {
                                throw new SaveException(
                                    'Failed to save the order\'s product item',
                                    $clonedItem->getMessages()
                                );
                            }
                        }
                    }

                    $cloned->quantity = 0;
                    $cloned->sub_total = 0;
                    $cloned->vat = 0;
                    $cloned->total = 0;

                    foreach ($cloned->items as $item) {
                        $cloned->quantity += $item->quantity;
                        $cloned->sub_total += $item->sub_total;
                        $cloned->vat += $item->vat;
                        $cloned->total += $item->total;
                    }

                    if ($cloned->update() === false) {
                        throw new SaveException(
                            'Failed to update the order',
                            $cloned->getMessages()
                        );
                    }

                    $address = $model->billing->clone([
                        'order_id' => $cloned->id
                    ]);
                    if ($address->save() === false) {
                        throw new SaveException(
                            'Failed to create the order\'s billing address',
                            $address->getMessages()
                        );
                    }

                    $address = $model->delivery->clone([
                        'order_id' => $cloned->id
                    ]);
                    if ($address->save() === false) {
                        throw new SaveException(
                            'Failed to create the order\'s delivery address',
                            $address->getMessages()
                        );
                    }

                    $this->addTags(['partial-order'], $cloned->id);
                    $this->addTags(['partial-order'], $model->id);
                    
                    $this->addNote('This is a partial order due to lack of stock', $cloned->id);
                    $this->addNote('This is a partial order due to lack of stock', $model->id);

                    $amount = $cloned->total;
                }
            }

            $shipping = new OrderShipping([
                'order_id' => $model->id,
                'shipping_company_code' => reset($_POST['shipping_company_code']),
                'shipping_option' => reset($_POST['shipping_option']),
                'parcel_count' => empty($_POST['requires_shipping']) ? intval($_POST['parcel_count']) : 1,
                'weight' => empty($_POST['requires_shipping']) ? floatval($_POST['weight']) : 0,
                'width' => empty($_POST['requires_shipping']) ? floatval($_POST['width']) : 0,
                'height' => empty($_POST['requires_shipping']) ? floatval($_POST['height']) : 0,
                'length' => empty($_POST['requires_shipping']) ? floatval($_POST['length']) : 0,
                'amount' => $amount,
            ]);

            if ($shipping->save() === false) {
                throw new SaveException(
                    'Failed to add the shipping method to the order',
                    $shipping->getMessages()
                );
            }
            
            $model->quantity = $quantity;
            $model->status = 'dispatched';
            $model->dispatched_at = Date('Y-m-d H:i:s');
            $model->dispatched_by = self::getUserId();

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the order',
                    $model->getMessages()
                );
            }

            foreach ($model->items as $item) {
                $item->product->stock -= $item->fulfilled;
                if ($item->product->update() === false) {
                    throw new SaveException(
                        'Failed to update the product',
                        $item->product->getMessages()
                    );
                }
                if ($item->product->stock < 0) {
                    $this->saveFormWarning(
                        'Product stock less than zero for ' .
                        '<a href="' . ($this->di->getConfig())->urls->sales . '/products/' . $item->product->id . '">' .
                        $item->product->name . "</a>"
                    );
                }
            }

            $this->saveFormUpdated('Shipping has been booked for the order');
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

    public function stats()
    {
        $model = new Orders();
        $table = $model->getSource();

        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE status = 'dispatch') AS pending,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'dispatched') AS dispatched,";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
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

        $url = $this->global_url . '/edit/' . $model->id;
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        try {
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

    private function validate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $required = [
            'shipping_company_code' => 'shipping company',
            'shipping_option' => 'shipping option',
            'weight' => 'weight',
            'width' => 'width',
            'height' => 'height',
            'length' => 'length',
            'parcel_count' => 'parcel_count',
            'amount' => 'amount',
        ];

        foreach ($required as $value => $string) {
            $validation->add(
                $value,
                new PresenceOf(
                    [
                        'message' => 'The ' . $string . ' is required',
                    ]
                )
            );
        }

        $required = [
            'weight' => 'weight',
            'width' => 'width',
            'height' => 'height',
            'length' => 'length',
            'parcel_count' => 'parcel_count',
            'amount' => 'amount',
        ];

        foreach ($required as $value => $string) {
            $validation->add(
                $value,
                new Digit(
                    [
                        'message' => 'The ' . $string . ' is not a valid number',
                    ]
                )
            );
        }

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Validation failed', $messages);
        }
    }
}
