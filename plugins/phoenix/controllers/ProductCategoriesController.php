<?php

/**
 * Product categories controller.
 *
 * @package     Kytschi\Phoenix\Controllers\ProductCategoriesController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Tengu\Controllers\Website\PageCategoriesController;

class ProductCategoriesController extends PageCategoriesController
{
    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/products/categories';
    public $resource = 'product-category';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->sales . $this->global_url;
    }

    public function addAction($title = 'Adding a product category', $template = 'website/pages/add')
    {
        return parent::addAction($title, $template);
    }

    public function editAction($id, $title = 'Managing the product category', $template = 'website/pages/edit')
    {
        return parent::editAction($id, $title, $template);
    }

    public function indexAction($title = 'Product categories', $template = 'website/page_categories/index')
    {
        return parent::indexAction($title, $template);
    }

    public function saveAction($ignore = true)
    {
        parent::saveAction($ignore);
    }
}
