<?php

/**
 * Front end routes.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

/**
 * Basket.
 */
$router->add(
    '/basket',
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'index'
    ]
);

$router->add(
    '/basket/add/{id}',
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'add',
        'id' => 1
    ]
);

$router->add(
    '/basket/checkout',
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'checkout'
    ]
);

$router->add(
    '/basket/checkout/complete',
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'complete'
    ]
);

$router->add(
    '/basket/clear',
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'clear'
    ]
);

$router->add(
    '/basket/delete/{id}',
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    '/basket/update/{id}',
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'update',
        'id' => 1
    ]
);

$router->setDefaults(
    [
        'controller' => 'Index',
        'action'     => 'fallback',
    ]
);
