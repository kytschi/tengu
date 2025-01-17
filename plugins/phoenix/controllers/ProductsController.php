<?php

/**
 * Products controller.
 *
 * @package     Kytschi\Phoenix\Controllers\ProductsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Makabe\Controllers\PersonasController;
use Kytschi\Phoenix\Models\Products;
use Kytschi\Phoenix\Models\Settings;
use Kytschi\Tengu\Controllers\Website\PageCategoriesController;
use Kytschi\Tengu\Controllers\Website\PagesController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Pages;
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

class ProductsController extends PagesController
{
    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/products';
    public $resource = 'product';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->sales . $this->global_url;
    }

    public function addAction($title = 'Adding a product', $template = 'website/pages/add')
    {
        $settings = Settings::findFirst([
            'id IS NOT NULL'
        ]);
        if (empty($settings)) {
            $settings = new Settings();
        }

        $this->view->setVars(
            [
                'settings' => $settings,
            ]
        );

        return parent::addAction($title, $template);
    }

    public function editAction($id, $title = 'Managing the product', $template = 'website/pages/edit')
    {
        $settings = Settings::findFirst([
            'id IS NOT NULL'
        ]);
        if (empty($settings)) {
            $settings = new Settings();
        }

        $this->view->setVars(
            [
                'settings' => $settings,
            ]
        );

        return parent::editAction($id, $title, $template);
    }

    public function indexAction($title = 'Products', $template = 'phoenix/products/index')
    {
        return parent::indexAction($title, $template);
    }

    public function saveSubAction($model)
    {
        $this->validateProduct();

        $product = new Products([
            'page_id' => $model->id,
            'price' => floatval($_POST['price']),
            'stock' => intval($_POST['stock']),
            'code' => $_POST['code'],
            'type' => in_array($_POST['type'], ['physical', 'digital-download']) ? $_POST['type'] : 'physical',
            'vat' => !empty($_POST['vat']) ? floatval($_POST['vat']) : 0,
            'low_stock' => !empty($_POST['low_stock']) ? intval($_POST['low_stock']) : 1,
            'requires_shipping' => !empty($_POST['requires_shipping']) ? 1 : 0,
        ]);

        if ($product->save() === false) {
            throw new SaveException(
                'Failed to create the product',
                $product->getMessages()
            );
        }
    }

    public function updateSubAction($model)
    {
        $this->validateProduct();

        $model->product->price = floatval($_POST['price']);
        $model->product->stock = intval($_POST['stock']);
        $model->product->vat = !empty($_POST['vat']) ? floatval($_POST['vat']) : 0;
        $model->product->code = $_POST['code'];
        $model->product->type = in_array($_POST['type'], ['physical', 'digital-download']) ? $_POST['type'] : 'physical';
        $model->product->low_stock = !empty($_POST['low_stock']) ? intval($_POST['low_stock']) : 1;
        $model->product->requires_shipping = !empty($_POST['requires_shipping']) ? 1 : 0;

        if ($model->product->update() === false) {
            throw new SaveException(
                'Failed to update the product',
                $model->product->getMessages()
            );
        }
    }

    private function validateProduct()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            'price',
            new PresenceOf(
                [
                    'message' => 'The price is required',
                ]
            )
        );

        $validation->add(
            'stock',
            new PresenceOf(
                [
                    'message' => 'The stock is required',
                ]
            )
        );

        $validation->add(
            'code',
            new PresenceOf(
                [
                    'message' => 'The code is required',
                ]
            )
        );

        $validation->add(
            'type',
            new PresenceOf(
                [
                    'message' => 'The type is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Validation failed', $messages);
        }
    }
}
