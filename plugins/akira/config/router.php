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
 * Appointments.
 */
$router->add(
    UrlHelper::backend($config->urls->css . '/appointments'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Appointments',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->css . '/appointments/create'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Appointments',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->css . '/appointments/cancel/{id}'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Appointments',
        'action'     => 'cancel',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->css . '/appointments/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Appointments',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->css . '/appointments/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Appointments',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->css . '/appointments/rebook/{id}'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Appointments',
        'action'     => 'rebook',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->css . '/appointments/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Appointments',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->css . '/appointments/save'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Appointments',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->css . '/appointments/update/{id}'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Appointments',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Dashboard.
 */
$router->add(
    UrlHelper::backend($config->urls->css . '/dashboard'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Dashboards',
        'action'     => 'index',
    ]
);
