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

$url = $config->urls->fms;

/*
 * Dashboard.
 */
$router->add(
    UrlHelper::backend($url . '/dashboard'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Dashboards',
        'action'     => 'index',
    ]
);

/**
 * Dividends.
 */
$router->add(
    UrlHelper::backend($url . '/dividends'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Dividends',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/dividends/create'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Dividends',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/dividends/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Dividends',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/dividends/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Dividends',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/dividends/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Dividends',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/dividends/save'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Dividends',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/dividends/update/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Dividends',
        'action'     => 'update',
        'id' => 1
    ]
);

/**
 * Invoices.
 */
$router->add(
    UrlHelper::backend($url . '/invoices'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Invoices',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/invoices/create'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Invoices',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/invoices/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Invoices',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/invoices/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Invoices',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/invoices/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Invoices',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/invoices/save'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Invoices',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/invoices/update/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Invoices',
        'action'     => 'update',
        'id' => 1
    ]
);

/**
 * Receipts.
 */
$router->add(
    UrlHelper::backend($url . '/receipts'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Receipts',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/receipts/add'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Receipts',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/receipts/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Receipts',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/receipts/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Receipts',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/receipts/parse/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Receipts',
        'action'     => 'parse',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/receipts/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Receipts',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/receipts/save'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Receipts',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/receipts/update/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Receipts',
        'action'     => 'update',
        'id' => 1
    ]
);

/**
 * Statements.
 */
$router->add(
    UrlHelper::backend($url . '/statements'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Statements',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/add'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Statements',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Statements',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Statements',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/parse/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Statements',
        'action'     => 'parse',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Statements',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/save'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Statements',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/update/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Statements',
        'action'     => 'update',
        'id' => 1
    ]
);

/**
 * Statement items.
 */
$router->add(
    UrlHelper::backend($url . '/statements/{statement_id}/items/add'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'StatementItems',
        'action'     => 'add',
        'statement_id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/{statement_id}/items/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'StatementItems',
        'action'     => 'delete',
        'statement_id' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/{statement_id}/items/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'StatementItems',
        'action'     => 'edit',
        'statement_id' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/{statement_id}/items/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'StatementItems',
        'action'     => 'recover',
        'statement_id' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/{statement_id}/items/save'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'StatementItems',
        'action'     => 'save',
        'statement_id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/statements/{statement_id}/items/update/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'StatementItems',
        'action'     => 'update',
        'statement_id' => 1,
        'id' => 2
    ]
);

/**
 * Settings.
 */
$router->add(
    UrlHelper::backend($url . '/settings'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Settings',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($url . '/settings/update'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'Settings',
        'action'     => 'update'
    ]
);

/**
 * Tax returns.
 */
$router->add(
    UrlHelper::backend($url . '/tax-returns'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxReturns',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-returns/create'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxReturns',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-returns/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxReturns',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-returns/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxReturns',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-returns/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxReturns',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-returns/save'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxReturns',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-returns/update/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxReturns',
        'action'     => 'update',
        'id' => 1
    ]
);

/**
 * Tax years.
 */
$router->add(
    UrlHelper::backend($url . '/tax-years'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxYears',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-years/add'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxYears',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-years/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxYears',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-years/download/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxYears',
        'action'     => 'download',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-years/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxYears',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-years/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxYears',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-years/save'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxYears',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/tax-years/update/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'TaxYears',
        'action'     => 'update',
        'id' => 1
    ]
);

/**
 * User tax codes.
 */
$router->add(
    UrlHelper::backend($url . '/user-tax-codes/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Wako\Controllers',
        'controller' => 'UserTaxCodes',
        'action'     => 'delete',
        'id' => 1
    ]
);
