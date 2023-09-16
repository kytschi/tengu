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

foreach ($config->apps as $app => $status) {
    if (file_exists($file = $config->application->pluginsDir . $app . '/config/routes/api.php')) {
        include $file;
    }
}

/*
 * Media.
 */
$router->add(
    UrlHelper::api('/files/images/{page}/{limit}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Files',
        'action'     => 'apiImages',
        'page' => 1,
        'limit' => 2
    ]
);

/*
 * Notifications.
 */
$router->add(
    UrlHelper::api('/users/notifications'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Users',
        'action'     => 'notifications',
    ]
);

/*
 * Postcodes.
 */
$router->add(
    UrlHelper::api('/postcodes/{postcode}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Postcodes',
        'action'     => 'search',
        'postcode' => 1
    ]
);

/*
 * Users.
 */
$router->add(
    UrlHelper::api('/users'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Core',
        'controller' => 'Users',
        'action'     => 'search'
    ]
);
