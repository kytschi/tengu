<?php

/**
 * Front end routes.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
