<?php

/**
 * Basket controller.
 *
 * @package     Kytschi\Phoenix\Controllers\BasketController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Phoenix\Controllers\CustomersController;
use Kytschi\Phoenix\Controllers\Payments\Gateways\StripeController;
use Kytschi\Phoenix\Controllers\OrdersController;
use Kytschi\Phoenix\Controllers\ShippingCompaniesController;
use Kytschi\Phoenix\Exceptions\StockException;
use Kytschi\Phoenix\Models\OrderAddresses;
use Kytschi\Phoenix\Models\OrderItems;
use Kytschi\Phoenix\Models\Orders;
use Kytschi\Phoenix\Models\PaymentGateway;
use Kytschi\Phoenix\Models\Products;
use Kytschi\Phoenix\Models\Settings;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\AuthorisationException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Security;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Encryption\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class BasketController extends ControllerBase
{
    use Form;
    use Logs;
    use Notes;
    use Security;
    use Tags;

    public $global_url = '/basket';
    public $resource = 'basket';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->sales . $this->global_url;
    }

    public function addAction($id)
    {
        $product = (new Products())->findFirst([
            'conditions' => 'page_id = :page_id:',
            'bind' => [
                'page_id' => $id
            ]
        ]);

        if (empty($product)) {
            return $this->notFound();
        }

        if (empty($basket = $this->get())) {
            $basket = $this->createBasket();
        }

        $settings = (new Settings())->findFirst([
            'conditions' => 'id IS NOT NULL'
        ]);

        $url = UrlHelper::backend($this->global_url);
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        try {
            $found = false;
            if (!empty($basket->items)) {
                foreach ($basket->items as $item) {
                    if ($item->product->page_id == $id) {
                        $quantity = 1;
                        if (!empty($_POST['quantity'])) {
                            if (!empty($_POST['quantity'][$id])) {
                                $quantity = intval($_POST['quantity'][$id]);
                            }
                        }

                        if (!$settings->zero_stock) {
                            if ($product->stock <= 0) {
                                throw new StockException('The product is out of stock');
                            }
                        }

                        $item->quantity += $quantity;
                        if ($item->update() === false) {
                            throw new SaveException(
                                'Failed to add the product to the basket',
                                $item->getMessages()
                            );
                        }

                        $this->updateItem($item);

                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $quantity = 1;
                if (!empty($_POST['quantity'])) {
                    if (is_array($_POST['quantity'])) {
                        if (!empty($_POST['quantity'][$id])) {
                            $quantity = intval($_POST['quantity'][$id]);
                        }
                    } else {
                        $quantity = intval($_POST['quantity']);
                    }
                }

                if (!$settings->zero_stock) {
                    if ($product->stock <= 0) {
                        throw new StockException('The product is out of stock');
                    }
                }

                $sub_total = $quantity * $product->price;
                $vat = $sub_total * (!empty($product->vat) ? ($product->vat / 100) : 0);

                $model = new OrderItems([
                    'order_id' => $basket->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'sub_total' => $sub_total,
                    'vat' => $vat,
                    'total' => $sub_total + $vat
                ]);

                if ($model->save() === false) {
                    throw new SaveException(
                        'Failed to add the product to the basket',
                        $model->getMessages()
                    );
                }
            }

            $this->update($basket);
            $this->saveFormUpdated('Item added to the basket');
        } catch (StockException $err) {
            $this->saveFormError($err->getMessage());
        } catch (Exception $err) {
            throw $err;
        }

        $this->redirect(rtrim($url, '/'));
    }

    public function archiveAction()
    {
        $this->clearFormData();
        $this->secure();

        $basket = $this->get();
        if (empty($basket)) {
            throw new SaveException('Failed due to no basket');
        }

        if (!self::isAdmin() && $basket->updated_by != self::getUserId()) {
            throw new AuthorisationException('This basket does not belong to you');
        }

        try {
            $basket->status = 'inactive';
            if ($basket->update() === false) {
                throw new SaveException(
                    'Failed to archive the basket',
                    $basket->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $basket->id,
                'info',
                'Basket archived by ' . $this->getUserFullName()
            );

            $url = $this->global_url;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Basket has been deleted');
            $this->redirect(
                UrlHelper::backend(
                    ($this->di->getConfig())->urls->sales . '/products'
                )
            );
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function checkoutAction()
    {
        $this->createPageObj('Checkout');

        if (TENGU_BACKEND) {
            $this->secure();
            $template = 'phoenix/basket/checkout/address';
            $url = UrlHelper::backend($this->global_url);
        } else {
            $template = 'basket/checkout/address';
            $url = '/basket';
        }

        if (empty($basket = $this->get())) {
            $this->redirect($url);
        }

        $this->setPageTitle('Checkout');

        return $this->view->partial(
            $template,
            [
                'basket' => $basket,
                'page' => $this->page_obj
            ]
        );
    }

    public function clearAction()
    {
        $this->clearFormData();
        $this->secure();

        $basket = $this->get();
        if (empty($basket)) {
            throw new SaveException('Failed due to no basket');
        }

        try {
            $basket->softDelete();

            $this->db->query(
                'UPDATE ' . (new OrderItems())->getSource() . ' 
                    SET 
                        deleted_at=NOW(),
                        deleted_by=:user_id 
                    WHERE 
                        order_id=:order_id',
                [
                    ':user_id' => self::getUserId(),
                    ':order_id' => $basket->id
                ]
            );

            $this->addLog(
                $this->resource,
                $basket->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $url = $this->global_url;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Basket has been deleted');
            $this->redirect(
                UrlHelper::backend(
                    ($this->di->getConfig())->urls->sales . '/products'
                )
            );
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function createAction($id)
    {
        if (empty($basket = $this->get())) {
            $this->createBasket($id);
        } elseif ($basket->customer_id != $id) {
            $this->createBasket($id);
        }

        $url = ($this->di->getConfig())->urls->sales . '/products';
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        $this->redirect(
            UrlHelper::backend(
                rtrim($url, '/')
            )
        );
    }

    private function createBasket($customer_id = null)
    {
        $user_id = self::getUserId();

        $basket = new Orders([
            'customer_id' => $customer_id,
            'number' => OrdersController::getNumber(),
            'status' => 'basket',
            'created_by' => $user_id,
            'updated_by' => $user_id,
        ]);

        if ($basket->save() === false) {
            throw new SaveException(
                'Failed to create the basket',
                $basket->getMessages()
            );
        }

        $this->session->set('basket', $basket->id);

        return $basket;
    }

    public function createCheckoutAction()
    {
        $basket = $this->get();
        if (empty($basket)) {
            throw new SaveException('Failed due to empty basket');
        }
        (new StripeController())->createCheckout($basket);
    }

    public function completeAction()
    {
        $this->createPageObj('Complete');

        if (TENGU_BACKEND) {
            $this->secure();
            $template = 'phoenix/basket/checkout/complete';
        } else {
            $template = 'basket/checkout/complete';
        }

        $this->setPageTitle('Complete');

        return $this->view->partial(
            $template,
            ['page' => $this->page_obj]
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();
        if (TENGU_BACKEND) {
            $this->secure();
        }

        $basket = $this->get();
        if (empty($basket)) {
            throw new SaveException('Failed due to empty basket');
        }

        try {
            $this->db->query(
                'UPDATE ' . (new OrderItems())->getSource() . ' 
                SET 
                    deleted_at=NOW(),
                    deleted_by=:user_id
                WHERE
                    product_id=:product_id AND order_id=:order_id',
                [
                    ':user_id' => self::getUserId(),
                    ':order_id' => $basket->id,
                    ':product_id' => $this->dispatcher->getParam('id')
                ]
            );

            $this->addLog(
                $this->resource,
                $this->dispatcher->getParam('id'),
                'danger',
                'Basket item deleted by ' . $this->getUserFullName()
            );

            $this->update($basket);

            $url = UrlHelper::backend($this->global_url);
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Basket item has been deleted');
            $this->redirect(rtrim($url, '/'));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function get()
    {
        $id = $this->session->basket;

        if (empty($id)) {
            return null;
        }

        $basket = Orders::findFirst([
            'conditions' => 'id = :id: AND status="basket"',
            'bind' => [
                'id' => $this->session->basket
            ]
        ]);

        return $basket;
    }

    public function indexAction()
    {
        $this->setPageTitle('Basket');

        $this->clearFormData();

        if (TENGU_BACKEND) {
            $this->secure();
            $template = 'phoenix/basket/index';
            $this->createPageObj('Basket');
            $page = $this->page_obj;
        } else {
            $template = 'basket/index';

            $path = parse_url($_SERVER['REQUEST_URI']);
            $url = rtrim($path['path'], '/');
            if (!$url) {
                $url = '/';
            }

            $page = Pages::query()
                ->where('deleted_at IS NULL AND (url = :url: OR canonical_url = :canonical_url:) AND status = "active"')
                ->bind([
                    'url' => $url,
                    'canonical_url' => $url
                ])
                ->limit(1)
                ->execute()
                ->getFirst();

            if (empty($page)) {
                $this->createPageObj('Basket');
                $page = $this->page_obj;
            }

            $page = $this->setPageTags($page);
        }

        return $this->view->partial(
            $template,
            [
                'basket' => $this->get(),
                'page' => $page
            ]
        );
    }

    private function processPayment($basket)
    {
        $gateway = PaymentGateway::findFirst([
            'condidtions' => 'id=:id',
            'bind' => [
                'id' => reset($_POST['payment_gateway'])
            ]
        ]);

        if (empty($gateway)) {
            throw new SaveException(
                'Failed to process the order',
                'Invalid gateway, ' . reset($_POST['payment_gateway'])
            );
        }

        switch ($gateway) {
            case 'stripe':
                (new StripeController())->save($basket);
                break;
        }
    }

    public function saveAddresses($basket)
    {
        $this->db->query(
            'UPDATE ' . (new OrderAddresses())->getSource() . ' 
            SET 
                deleted_at=NOW(),
                deleted_by=:user_id
            WHERE
                order_id=:order_id',
            [
                ':user_id' => self::getUserId(),
                ':order_id' => $basket->id
            ]
        );

        $address = new OrderAddresses([
            'order_id' => $basket->id,
            'type' => 'billing',
            'first_name' => $_POST['billing_first_name'],
            'last_name' => $_POST['billing_last_name'],
            'email' => $_POST['billing_email'],
            'phone' => !empty($_POST['billing_phone']) ? $_POST['billing_phone'] : '',
            'company' => !empty($_POST['billing_company']) ? $_POST['billing_company'] : '',
            'address_line_1' => $_POST['billing_address_line_1'],
            'address_line_2' => !empty($_POST['billing_address_line_2']) ? $_POST['billing_address_line_2'] : '',
            'town' => $_POST['billing_town'],
            'county' => $_POST['billing_county'],
            'country' => $_POST['billing_country'],
            'postcode' => $_POST['billing_postcode']
        ]);

        if ($address->save() === false) {
            throw new SaveException(
                'Failed to create the order\'s billing address',
                $address->getMessages()
            );
        }

        $customer = (new CustomersController())->add(
            [
                'first_name' => $_POST['billing_first_name'],
                'last_name' => $_POST['billing_last_name'],
                'email' => $_POST['billing_email'],
                'phone' => !empty($_POST['billing_phone']) ? $_POST['billing_phone'] : '',
                'company' => !empty($_POST['billing_company']) ? $_POST['billing_company'] : '',
                'address_line_1' => $_POST['billing_address_line_1'],
                'address_line_2' => !empty($_POST['billing_address_line_2']) ? $_POST['billing_address_line_2'] : '',
                'town' => $_POST['billing_town'],
                'county' => $_POST['billing_county'],
                'country' => $_POST['billing_country'],
                'postcode' => $_POST['billing_postcode']
            ]
        );

        $basket->customer_id = $customer->id;
        if ($basket->save() === false) {
            throw new SaveException(
                'Failed to save the customer against the order',
                $basket->getMessages()
            );
        }

        if (!empty($_POST['same_billing'])) {
            $address = new OrderAddresses([
                'order_id' => $basket->id,
                'type' => 'delivery',
                'first_name' => $_POST['billing_first_name'],
                'last_name' => $_POST['billing_last_name'],
                'email' => $_POST['billing_email'],
                'phone' => !empty($_POST['billing_phone']) ? $_POST['billing_phone'] : '',
                'company' => !empty($_POST['billing_company']) ? $_POST['billing_company'] : '',
                'address_line_1' => $_POST['billing_address_line_1'],
                'address_line_2' => !empty($_POST['billing_address_line_2']) ? $_POST['billing_address_line_2'] : '',
                'town' => $_POST['billing_town'],
                'county' => $_POST['billing_county'],
                'country' => $_POST['billing_country'],
                'postcode' => $_POST['billing_postcode']
            ]);
        } else {
            $address = new OrderAddresses([
                'order_id' => $basket->id,
                'type' => 'delivery',
                'first_name' => $_POST['delivery_first_name'],
                'last_name' => $_POST['delivery_last_name'],
                'email' => $_POST['delivery_email'],
                'phone' => !empty($_POST['delivery_phone']) ? $_POST['delivery_phone'] : '',
                'company' => !empty($_POST['delivery_company']) ? $_POST['delivery_company'] : '',
                'address_line_1' => $_POST['delivery_address_line_1'],
                'address_line_2' => !empty($_POST['delivery_address_line_2']) ? $_POST['delivery_address_line_2'] : '',
                'town' => $_POST['delivery_town'],
                'county' => $_POST['delivery_county'],
                'country' => $_POST['delivery_country'],
                'postcode' => $_POST['delivery_postcode']
            ]);
        }

        if ($address->save() === false) {
            throw new SaveException(
                'Failed to create the order\'s delivery address',
                $address->getMessages()
            );
        }
    }

    public function takePaymentAction()
    {
        $this->createPageObj('Checkout');

        if (TENGU_BACKEND) {
            $this->secure();
            $template = 'phoenix/basket/checkout/payment';
            $url = UrlHelper::backend($this->global_url);
        } else {
            $template = 'basket/checkout/payment';
            $url = '/basket';
        }

        if (empty($basket = $this->get())) {
            $this->redirect($url);
        }

        $this->setPageTitle('Processing payment');

        return $this->view->partial(
            $template,
            [
                'basket' => $basket,
                'page' => $this->page_obj
            ]
        );
    }

    public function updateAction()
    {
        if (TENGU_BACKEND) {
            $this->secure();
            $url = $this->global_url;
        } else {
            $url = '/basket';
        }

        $basket = $this->get();
        if (empty($basket)) {
            throw new SaveException('Failed due to empty basket');
        }

        foreach ($basket->items as $item) {
            if (empty($_POST['quantity'])) {
                continue;
            }

            if (empty($quantity = $_POST['quantity'][$item->id])) {
                continue;
            }

            if ($item->product->stock < $quantity) {
                $quantity = $item->product->stock;
                $this->saveFormWarning('One of the products does not have enough stock for that quantity');
            }

            $item->quantity = $quantity;
            $this->updateItem($item);
        }

        $this->update($basket);

        try {
            $this->addNoteFromRequest($basket->id);

            if (isset($_POST['checkout'])) {
                if (TENGU_BACKEND) {
                    $url = $this->global_url . '/checkout';
                } else {
                    $url = '/basket/checkout';
                }
            } elseif (isset($_POST['payment_gateway'])) {
                if (TENGU_BACKEND) {
                    $url = $this->global_url . '/checkout';
                } else {
                    $url = '/basket/checkout';
                }

                $this->validateAddress();
                $this->saveAddresses($basket);

                if (TENGU_BACKEND) {
                    $url = $this->global_url . '/checkout/payment';
                } else {
                    $url = '/basket/checkout/payment';
                }
            } else {
                $this->saveFormUpdated('Basket has been updated');
            }
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
        }

        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        $this->redirect(rtrim($url, '/'));
    }

    private function update($basket = null)
    {
        if (!$basket) {
            $basket = $this->get();
        }

        $basket->quantity = 0;
        $basket->sub_total = 0;
        $basket->vat = 0;
        $basket->total = 0;

        foreach ($basket->items as $item) {
            $basket->quantity += $item->quantity;
            $basket->sub_total += $item->sub_total;
            $basket->vat += $item->vat;
            $basket->total += $item->total;
        }

        if ($basket->update() === false) {
            throw new SaveException(
                'Failed to update the basket',
                $basket->getMessages()
            );
        }
    }

    private function updateItem($item)
    {
        $item->sub_total = $item->quantity * $item->product->price;
        $item->vat = $item->sub_total * (!empty($item->product->vat) ? ($item->product->vat / 100) : 0);
        $item->total = $item->sub_total + $item->vat;

        if ($item->save() === false) {
            throw new SaveException(
                'Failed to update the product in the basket',
                $item->getMessages()
            );
        }
    }

    private function validateAddress()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $required = [
            'billing_first_name',
            'billing_last_name',
            'billing_email',
            'billing_address_line_1',
            'billing_postcode',
            'billing_town',
            'billing_county',
            'billing_country',
        ];

        if (empty($_POST['same_billing'])) {
            $required = array_merge(
                $required,
                [
                    'delivery_first_name',
                    'delivery_last_name',
                    'delivery_email',
                    'delivery_address_line_1',
                    'delivery_postcode',
                    'delivery_town',
                    'delivery_county',
                    'delivery_country',
                ]
            );
        }

        foreach ($required as $require) {
            $validation->add(
                $require,
                new PresenceOf(
                    [
                        'message' => 'The ' . str_replace('_', ' ', $require) . ' is required',
                    ]
                )
            );
        }

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException(
                'Validation failed',
                $required
            );
        }
    }
}
