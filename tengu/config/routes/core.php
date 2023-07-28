<?php

/**
 * Core routes.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

use Kytschi\Tengu\Helpers\UrlHelper;

/*
 * Clients.
 */
$router->add(
    UrlHelper::backend('/clients'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Clients',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend('/clients/add'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Clients',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend('/clients/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Clients',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/clients/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Clients',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/clients/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Clients',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/clients/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Clients',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend('/clients/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Clients',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Dashboards.
 */
$router->add(
    UrlHelper::backend('/'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Dashboards',
        'action'     => 'home',
    ]
);

$router->add(
    UrlHelper::backend('/dashboard'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Dashboards',
        'action'     => 'home',
    ]
);

/*
 * Files.
 */
$router->add(
    UrlHelper::backend('/files/add/image'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Form',
        'action'     => 'addAjaxImage'
    ]
);

$router->add(
    UrlHelper::backend('/files/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Files',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/files/download/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Files',
        'action'     => 'download',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/files/output/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Files',
        'action'     => 'output',
        'id' => 1
    ]
);

/*
 * Groups.
 */
$router->add(
    UrlHelper::backend('/groups'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Groups',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend('/groups/add'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Groups',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend('/groups/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Groups',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/groups/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Groups',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/groups/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Groups',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/groups/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Groups',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend('/groups/system'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Groups',
        'action'     => 'system',
    ]
);

$router->add(
    UrlHelper::backend('/groups/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Groups',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Login
 */
$router->add(
    UrlHelper::backend('/login'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Login',
        'action'     => 'login',
    ]
);

$router->add(
    UrlHelper::backend('/logout'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Login',
        'action'     => 'logout',
    ]
);

/*
 * Logs.
 */
$router->add(
    UrlHelper::backend('/logs'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Logs',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend('/logs/clear'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Logs',
        'action'     => 'clear'
    ]
);

$router->add(
    UrlHelper::backend('/logs/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Logs',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/logs/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Logs',
        'action'     => 'edit',
        'id' => 1
    ]
);

/*
 * Messages.
 */
$router->add(
    UrlHelper::backend('/messages/inbox'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Communications',
        'action'     => 'inbox',
    ]
);

$router->add(
    UrlHelper::backend('/messages/sent'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Communications',
        'action'     => 'sent',
    ]
);

$router->add(
    UrlHelper::backend('/messages/trash'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Communications',
        'action'     => 'trash',
    ]
);

$router->add(
    UrlHelper::backend('/messages/inbox'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Communications',
        'action'     => 'inbox',
    ]
);

$router->add(
    UrlHelper::backend('/messages/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Communications',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/messages/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Communications',
        'action'     => 'recover',
        'id' => 1
    ]
);

/*
 * Notifications.
 */
$router->add(
    UrlHelper::backend('/notifications'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Notifications',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend('/notifications/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Notifications',
        'action'     => 'delete',
        'id' => 1
    ]
);

/*
 * Notes.
 */
$router->add(
    UrlHelper::backend('/notes/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Notes',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/notes/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Notes',
        'action'     => 'recover',
        'id' => 1
    ]
);

/*
 * Queue.
 */
$router->add(
    UrlHelper::backend('/queue'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Queue',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend('/queue/clear-{status}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Queue',
        'action'     => 'clear',
        'status' => 1
    ]
);

$router->add(
    UrlHelper::backend('/queue/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Queue',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/queue/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Queue',
        'action'     => 'edit',
        'id' => 1
    ]
);

/*
 * Search
 */
$router->add(
    UrlHelper::backend('/search'),
    [
        'controller' => 'Index',
        'action'     => 'search',
    ]
);

/*
 * Settings
 */
$router->add(
    UrlHelper::backend('/settings'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Settings',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend('/settings/update'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Settings',
        'action'     => 'update',
    ]
);

/*
 * Users.
 */
$router->add(
    UrlHelper::backend('/users'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Users',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend('/users/add'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Users',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend('/users/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Users',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/users/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Users',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/users/profile'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Users',
        'action'     => 'profile',
    ]
);

$router->add(
    UrlHelper::backend('/users/profile-save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Users',
        'action'     => 'profileSave',
    ]
);

$router->add(
    UrlHelper::backend('/users/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Users',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend('/users/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Users',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend('/users/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Users',
        'action'     => 'update',
        'id' => 1
    ]
);
