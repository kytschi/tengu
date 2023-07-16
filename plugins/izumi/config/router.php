<?php

/**
 * Routes.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

use Phalcon\Mvc\Router;
use Kytschi\Tengu\Helpers\UrlHelper;

$url = '/sms';

/*
 * Dashboard.
 */
$router->add(
    UrlHelper::backend($url . '/dashboard'),
    [
        'namespace'  => 'Kytschi\Izumi\Controllers',
        'controller' => 'Dashboards',
        'action'     => 'index',
    ]
);
