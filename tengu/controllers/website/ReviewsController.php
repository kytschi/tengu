<?php

/**
 * Reviews controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\ReviewsController
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

use Kytschi\Makabe\Controllers\PersonasController;
use Kytschi\Tengu\Controllers\Website\PageCategoriesController;
use Kytschi\Tengu\Controllers\Website\PagesController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\StringHelper;
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

class ReviewsController extends PagesController
{
    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/reviews';
    public $resource = 'review';
    public $category_support = false;

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
        $this->global_add_url = $this->global_url . '/create';
    }

    public function addAction($title = 'Build a review', $template = 'website/pages/add')
    {
        return parent::addAction($title, $template);
    }

    public function editAction($id, $title = 'Managing the review', $template = 'website/pages/edit')
    {
        return parent::editAction($id, $title, $template);
    }

    public function indexAction($title = 'Reviews', $template = 'website/pages/index')
    {
        return parent::indexAction($title, $template);
    }

    public function setDataSubAction($model)
    {
        if (empty($_POST['url'])) {
            $model->url = '/reviews/' . StringHelper::random(16);
        }
        return $model;
    }
}
