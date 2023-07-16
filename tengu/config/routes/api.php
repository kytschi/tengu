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

foreach ($config->apps as $app => $status) {
    if (file_exists($file = $config->application->pluginsDir . $app . '/config/routes/api.php')) {
        include $file;
    }
}

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
