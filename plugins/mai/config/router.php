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

$url = $config->urls->hrs;

/*
 * Dashboard.
 */
$router->add(
    UrlHelper::backend($url . '/dashboard'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Dashboards',
        'action'     => 'index',
    ]
);


/*
 * Projects.
 */
$router->add(
    UrlHelper::backend($url . '/projects'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Projects',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/add'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Projects',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Projects',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Projects',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Projects',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/save'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Projects',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/projects/update/{id}'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Projects',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Timesheets.
 */
$router->add(
    UrlHelper::backend($url . '/timesheets'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Timesheets',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/timesheets/create'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Timesheets',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/timesheets/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Timesheets',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/timesheets/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Timesheets',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/timesheets/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Timesheets',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/timesheets/save'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Timesheets',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/timesheets/update/{id}'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'Timesheets',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Timesheet entries.
 */
$router->add(
    UrlHelper::backend($url . '/timesheets/entries/save'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'TimesheetEntries',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/timesheets/entries/update/{id}'),
    [
        'namespace'  => 'Kytschi\Mai\Controllers',
        'controller' => 'TimesheetEntries',
        'action'     => 'update',
        'id' => 1
    ]
);
