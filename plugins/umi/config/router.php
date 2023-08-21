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

$url = $config->urls->pms;

/*
 * Dashboard.
 */
$router->add(
    UrlHelper::backend($url . '/dashboard'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'Dashboards',
        'action'     => 'index',
    ]
);

/*
 * My board.
 */
$router->add(
    UrlHelper::backend($url . '/my-board'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'MyBoard',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/my-board/add/{id}'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'MyBoard',
        'action'     => 'add',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/my-board/create'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'MyBoard',
        'action'     => 'create',
    ]
);

/*
 * Board columns.
 */
$router->add(
    UrlHelper::backend($url . '/boards/columns/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'BoardColumns',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/boards/columns/update'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'BoardColumns',
        'action'     => 'update'
    ]
);

$router->add(
    UrlHelper::backend($url . '/boards/columns/update-entries/{id}'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'BoardColumns',
        'action'     => 'updateEntries',
        'id' => 1
    ]
);

/*
 * Board entries.
 */
$router->add(
    UrlHelper::backend($url . '/boards/entries/add/{id}'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'BoardEntries',
        'action'     => 'add',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/boards/entries/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'BoardEntries',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/boards/entries/update'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'BoardEntries',
        'action'     => 'update',
    ]
);

/*
 * Projects.
 */
$router->add(
    UrlHelper::backend($url . '/projects'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'Projects',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/add'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'Projects',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'Projects',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'Projects',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'Projects',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/save'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'Projects',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/update/{id}'),
    [
        'namespace'  => 'Kytschi\Umi\Controllers',
        'controller' => 'Projects',
        'action'     => 'update',
        'id' => 1
    ]
);
