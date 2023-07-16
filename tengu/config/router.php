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
