<?php

/**
 * Api routes.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

use Kytschi\Tengu\Helpers\UrlHelper;

/*
 * Invoices.
 */
$router->add(
    UrlHelper::api('/invoices'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Invoices',
        'action'     => 'search',
    ]
);

/*
 * Receipts.
 */
$router->add(
    UrlHelper::api('/receipts'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Receipts',
        'action'     => 'search',
    ]
);
