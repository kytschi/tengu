<?php

/**
 * Routes.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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

/*
 * Event categories
 */
$router->add(
    UrlHelper::backend($config->urls->ems . '/events/categories'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'EventCategories',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/categories/create'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'EventCategories',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/categories/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'EventCategories',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/categories/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'EventCategories',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/categories/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'EventCategories',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/categories/save'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'EventCategories',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->ems . '/events/categories/update/{id}'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'EventCategories',
        'action'     => 'update',
        'id' => 1
    ]
);
