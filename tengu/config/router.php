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

$router = $di->getRouter(false);
$router->setDefaultNamespace('Kytschi\Tengu\Controllers');
$router->removeExtraSlashes(true);

if (TENGU_API) {
    include $config->application->appDir . 'config/routes/api.php';
} elseif (TENGU_BACKEND) {
    foreach ($config->apps as $app => $status) {
        if (file_exists($file = $config->application->pluginsDir . $app . '/config/router.php')) {
            include $file;
        }
    }

    include $config->application->appDir . 'config/routes/core.php';
    include $config->application->appDir . 'config/routes/website.php';

    $router->add(
        UrlHelper::backend('/errors/access-denined'),
        [
            'controller' => 'Errors',
            'action'     => 'accessDenined',
        ]
    );

    $router->add(
        UrlHelper::backend('/errors/critical'),
        [
            'controller' => 'Errors',
            'action'     => 'critical',
        ]
    );

    $router->add(
        UrlHelper::backend('/errors/save'),
        [
            'controller' => 'Errors',
            'action'     => 'save',
        ]
    );
}

/**
 * Errors.
 */
$router->add(
    '/errors/access-denined',
    [
        'controller' => 'Errors',
        'action'     => 'accessDenined',
    ]
);

$router->add(
    '/errors/critical',
    [
        'controller' => 'Errors',
        'action'     => 'critical',
    ]
);

$router->add(
    '/errors/save',
    [
        'controller' => 'Errors',
        'action'     => 'save',
    ]
);

/**
 * Sales app frontend routes.
 */
include $config->application->pluginsDir . 'phoenix/config/website_routes.php';

/**
 * Marketing app frontend routes.
 */
include $config->application->pluginsDir . 'makabe/config/website_routes.php';

/*
 * Robots text
 */
$router->add(
    '/robots.txt',
    [
        'controller' => 'Index',
        'action'     => 'robots',
    ]
);

/*
 * Sitemap.
 */
$router->add(
    '/sitemap.xml',
    [
        'controller' => 'Index',
        'action'     => 'sitemap'
    ]
);

$router->handle($_SERVER['REQUEST_URI']);
