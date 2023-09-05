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

$url = $config->urls->sales;

/*
 * Basket
 *
 */
$router->add(
    UrlHelper::backend($url . '/basket'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($url . '/basket/add/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'add',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/basket/archive'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'archive'
    ]
);

$router->add(
    UrlHelper::backend($url . '/basket/checkout'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'checkout'
    ]
);

$router->add(
    UrlHelper::backend($url . '/basket/checkout/complete'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'complete'
    ]
);

$router->add(
    UrlHelper::backend($url . '/basket/clear'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'clear'
    ]
);

$router->add(
    UrlHelper::backend($url . '/basket/create/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'create',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/basket/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/basket/update/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Basket',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Categories
 */
$router->add(
    UrlHelper::backend($url . '/products/categories'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'ProductCategories',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/categories/add'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'ProductCategories',
        'action'     => 'add'
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/categories/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'ProductCategories',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/categories/save'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'ProductCategories',
        'action'     => 'save'
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/categories/update/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'ProductCategories',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Customers.
 *
 */
$router->add(
    UrlHelper::backend($url . '/customers'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Customers',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($url . '/customers/create'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Customers',
        'action'     => 'add'
    ]
);

$router->add(
    UrlHelper::backend($url . '/customers/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Customers',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/customers/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Customers',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/customers/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Customers',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/customers/update/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Customers',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Dashboard.
 */
$router->add(
    UrlHelper::backend($url . '/dashboard'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Dashboards',
        'action'     => 'index',
    ]
);

/*
 * Dispatch.
 *
 */
$router->add(
    UrlHelper::backend($url . '/dispatch'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Dispatch',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($url . '/dispatch/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Dispatch',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/dispatch/flag'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Dispatch',
        'action'     => 'flag',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/dispatch/shipping'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Dispatch',
        'action'     => 'shipping'
    ]
);

/*
 * Orders.
 *
 */
$router->add(
    UrlHelper::backend($url . '/orders'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Orders',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($url . '/orders/clone/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Orders',
        'action'     => 'clone',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/orders/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Orders',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/orders/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Orders',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/orders/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Orders',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/orders/resume/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Orders',
        'action'     => 'resume',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/orders/update/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Orders',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Products.
 */
$router->add(
    UrlHelper::backend($url . '/products'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Products',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/create'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Products',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Products',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Products',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Products',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/restock'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Restock',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/restock/update'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Restock',
        'action'     => 'update',
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/save'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Products',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/products/update/{id}'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Products',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Settings
 */
$router->add(
    UrlHelper::backend($url . '/settings'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Settings',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($url . '/settings/update'),
    [
        'namespace'  => 'Kytschi\Phoenix\Controllers',
        'controller' => 'Settings',
        'action'     => 'update'
    ]
);
