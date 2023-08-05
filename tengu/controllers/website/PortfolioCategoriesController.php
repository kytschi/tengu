<?php

/**
 * Portfolio Categories controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\PortfolioCategoriesController
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

namespace Kytschi\Tengu\Controllers\Website;

use Kytschi\Tengu\Controllers\Website\PageCategoriesController;

class PortfolioCategoriesController extends PageCategoriesController
{
    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/portfolio/categories';
    public $global_from_url = '/portfolio';
    public $resource = 'portfolio-category';
    public $category_support = false;

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
        $this->global_from_url = ($this->di->getConfig())->urls->cms . $this->global_from_url;
        $this->global_add_url = $this->global_url . '/add';
        $this->global_category_url = $this->global_url;
    }

    public function addAction($title = 'Adding a portfolio category', $template = 'website/pages/add')
    {
        return parent::addAction($title, $template);
    }

    public function editAction($id, $title = 'Managing the portfolio category', $template = 'website/pages/edit')
    {
        return parent::editAction($id, $title, $template);
    }

    public function indexAction($title = 'Portfolio categories', $template = 'website/page_categories/index')
    {
        return parent::indexAction($title, $template);
    }
}
