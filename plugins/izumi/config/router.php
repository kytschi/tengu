<?php

/**
 * Routes.
 *
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

use Phalcon\Mvc\Router;
use Kytschi\Tengu\Helpers\UrlHelper;

/*
 * Dashboard.
 */
$router->add(
    UrlHelper::backend($config->urls->ems . '/dashboard'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'Dashboards',
        'action'     => 'index',
    ]
);

/*
 * Events.
 */
$router->add(
    UrlHelper::backend($config->urls->ems . '/events'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'Events',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/create'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'Events',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'Events',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'Events',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'Events',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/save'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'Events',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/update/{id}'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'Events',
        'action'     => 'update',
        'id' => 1
    ]
);
