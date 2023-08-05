<?php

/**
 * Event categories controller.
 *
 * @package     Kytschi\Izumi\Controllers\EventCategoriesController
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

namespace Kytschi\Izumi\Controllers;

use Kytschi\Izumi\Controllers\EventsController;
use Kytschi\Tengu\Controllers\Core\PostcodesController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class EventCategoriesController extends EventsController
{
    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/events/categories';
    public $global_from_url = '/events';
    public $resource = 'event-category';
    public $category_support = false;

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->ems . $this->global_url;
        $this->global_from_url = ($this->di->getConfig())->urls->ems . $this->global_from_url;
        $this->global_add_url = $this->global_url . '/create';
        $this->global_category_url = $this->global_url;
    }

    public function addAction($title = 'Add an event category', $template = 'website/pages/add')
    {
        parent::addAction($title, $template);
    }

    public function editAction($id, $title = 'Editing the event category', $template = 'website/pages/edit')
    {
        parent::editAction($id, $title, $template);
    }

    public function indexAction($title = 'Our event categories', $template = 'website/pages/index')
    {
        return parent::indexAction($title, $template);
    }
}
