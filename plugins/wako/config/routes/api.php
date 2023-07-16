<?php

/**
 * Api routes.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
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
