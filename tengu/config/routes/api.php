<?php

/**
 * Api routes.
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
