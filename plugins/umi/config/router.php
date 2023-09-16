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
