<?php

/**
 * Product categories controller.
 *
 * @package     Kytschi\Phoenix\Controllers\ProductCategoriesController
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

    public function addAction($template = 'true')
    {
        $this->setPageTitle('Adding a product category');
        return parent::addAction();
    }

    public function editAction($id, $template = 'true')
    {
        $this->setPageTitle('Managing the product category');
        return parent::editAction($id);
    }

    public function indexAction($template = 'true')
    {
        $this->setPageTitle('Product categories');
        return parent::indexAction();
    }

    public function saveAction($ignore = true)
    {
        parent::saveAction($ignore);
    }
}
