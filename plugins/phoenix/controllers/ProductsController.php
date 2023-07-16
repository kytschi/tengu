<?php

/**
 * Products controller.
 *
 * @package     Kytschi\Phoenix\Controllers\ProductsController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
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
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

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

    public function addAction($template = 'phoenix/products/add')
    {
        $this->setPageTitle('Adding a product');

        $this->view->setVars(
            [
                'settings' => (new Settings())->findFirst([
                    'id IS NOT NULL'
                ]),
            ]
        );

        return parent::addAction($template);
    }

    public function editAction($id, $template = 'phoenix/products/edit')
    {
        $this->setPageTitle('Managing the product');
        return parent::editAction($id, $template);
    }

    public function indexAction($template = 'phoenix/products/index')
    {
        $this->setPageTitle('Products');

        return parent::indexAction($template);
    }

    public function saveSubAction($model)
    {
        $this->validateProduct();

        $product = new Products([
            'page_id' => $model->id,
            'price' => floatval($_POST['price']),
            'stock' => intval($_POST['stock']),
            'code' => $_POST['code'],
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

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Validation failed', $messages);
        }
    }
}
