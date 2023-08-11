<?php

/**
 * Config.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

$apps = [
    'akira' => false,
    'izumi' => false,
    'makabe' => false,
    'mai' => false,
    'umi' => false,
    'wako' => false,
    'phoenix' => false
];

if ($_ENV['APP_CUSTOMER_SUPPORT'] == 'true') {
    $apps['akira'] = true;
}

if ($_ENV['APP_EVENTS'] == 'true') {
    $apps['izumi'] = true;
}

if ($_ENV['APP_MARKETING'] == 'true') {
    $apps['makabe'] = true;
}

if ($_ENV['APP_HR'] == 'true') {
    $apps['mai'] = true;
}

if ($_ENV['APP_DEVELOPMENT'] == 'true') {
    $apps['umi'] = true;
}

if ($_ENV['APP_FINANCE'] == 'true') {
    $apps['wako'] = true;
}

if ($_ENV['APP_SALES'] == 'true') {
    $apps['phoenix'] = true;
}

$urls = [
    'cms' => '/cms',
    'pms' => '/pms',
    'fms' => '/fms',
    'ems' => '/ems',
    'mms' => '/mms',
    'css' => '/css',
    'hrs' => '/hrs',
    'sales' => '/sales'
];

$global_cfg = [
    'database' => [
        'adapter'     => $_ENV['DB_CONNECTION'],
        'host'        => $_ENV['DB_HOST'],
        'port'        => $_ENV['DB_PORT'],
        'username'    => $_ENV['DB_USERNAME'],
        'password'    => $_ENV['DB_PASSWORD'],
        'dbname'      => $_ENV['DB_DATABASE'],
        'charset'     => 'utf8',
    ],
    'redis' => [
        'host'        => $_ENV['REDIS_HOST'],
        'port'        => $_ENV['REDIS_PORT'],
    ],
    'application' => [
        'appDir'         => APP_PATH . '/',
        'appUrl' => ($_ENV['APP_HTTPS'] == 'true' ? 'https://' : 'http://') . $_ENV['APP_SITE_DOMAIN'],
        'controllersDir' => APP_PATH . '/controllers/',
        'modelsDir'      => APP_PATH . '/models/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'viewsDir'       => APP_PATH . '/views/',
        'genericViewsDir' => APP_PATH . '/views/generic/',
        'siteViewsDir'   => BASE_PATH . '/public/website/views/',
        'pluginsDir'     => BASE_PATH . '/plugins/',
        'libraryDir'     => APP_PATH . '/library/',
        'cacheDir'       => BASE_PATH . '/cache/',
        'tmpDir' => BASE_PATH . '/.tmp/',
        'themesDir'      => BASE_PATH . '/public/website/themes/',
        'themesWebURL'   => '/website/themes/',
        'tenguThemesDir'      => BASE_PATH . '/public/assets/themes/',
        'tenguThemesWebURL'   => '/assets/themes/',
        'tenguAssetDir'      => BASE_PATH . '/public/assets/',
        'tenguAssetWebURL'   => '/assets/',
        'dumpDir'        => BASE_PATH . '/public/dump/',
        'dumpUri'        => '/dump/',
        'baseUri'        => '/',
        'downloadFileUri' => '/files/download',
        'outputFileUri' => '/files/output',
        'srcDir'         => BASE_PATH . '/public/website/src/',
        'jobsDir'        => APP_PATH . '/jobs/',
        'traitsDir'      => APP_PATH . '/traits/',
        'assetsDir'      => BASE_PATH . '/public/assets/',
    ],
    'apps' => $apps,
    'urls' => $urls
];

foreach ($apps as $app => $status) {
    if (!$status) {
        continue;
    }
    if (file_exists($file = BASE_PATH . '/plugins/' . $app . '/config/config.php')) {
        include $file;

        if (!empty($url)) {
            $urls = array_merge($urls, $url);
            $url = null;
        }

        if (!empty($cfg)) {
            $global_cfg = array_merge($global_cfg, $cfg);
            $cfg = null;
        }
    }
}

return new \Phalcon\Config\Config($global_cfg);
