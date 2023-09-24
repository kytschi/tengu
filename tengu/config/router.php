<?php

/**
 * Routes.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
 * Ads text
 */
$router->add(
    '/ads.txt',
    [
        'controller' => 'Index',
        'action'     => 'ads',
    ]
);

/*
 * Humans text
 */
$router->add(
    '/humans.txt',
    [
        'controller' => 'Index',
        'action'     => 'humans',
    ]
);

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
 * RSS
 */
$router->add(
    '/feed',
    [
        'controller' => 'Index',
        'action'     => 'rss'
    ]
);
$router->add(
    '/comments/feed',
    [
        'controller' => 'Index',
        'action'     => 'rss'
    ]
);
$router->add(
    '/news/feed',
    [
        'controller' => 'Index',
        'action'     => 'rss'
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
$router->add(
    '/sitemaps.xml',
    [
        'controller' => 'Index',
        'action'     => 'sitemap'
    ]
);
$router->add(
    '/post-sitemap.xml',
    [
        'controller' => 'Index',
        'action'     => 'sitemap'
    ]
);
$router->add(
    '/wlwmanifest.xml',
    [
        'controller' => 'Index',
        'action'     => 'sitemap'
    ]
);
$router->add(
    '/author-sitemap.xml',
    [
        'controller' => 'Index',
        'action'     => 'sitemap'
    ]
);

$router->handle($_SERVER['REQUEST_URI']);
