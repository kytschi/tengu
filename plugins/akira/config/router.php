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
 * Appointment settings.
 */
$router->add(
    UrlHelper::backend($config->urls->css . '/appointments/settings'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Settings',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->css . '/appointments/settings/update'),
    [
        'namespace'  => 'Kytschi\Akira\Controllers',
        'controller' => 'Settings',
        'action'     => 'update'
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
