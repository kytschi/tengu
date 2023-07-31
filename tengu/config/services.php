<?php

/**
 * Services.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

use Phalcon\Cache\AdapterFactory as CacheAdapter;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Model\MetaData\Redis as MetaRedis;
use Phalcon\Session\Manager as SessionManager;
use Phalcon\Session\Adapter\Redis;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Storage\AdapterFactory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Mvc\Url as UrlResolver;

/*
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/*
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/*
 * Setting up the view component
 */
$di->setShared('view', function () {
    $config = $this->getConfig();

    $view = new View();
    $view->setDI($this);

    if (!empty($_SERVER['REQUEST_URI'])) {
        if (TENGU_BACKEND) {
            $folders = [
                $config->application->viewsDir,
                $config->application->genericViewsDir
            ];

            foreach ($config->apps as $app => $status) {
                if (
                    file_exists(
                        $file = $config->application->pluginsDir . $app . '/config/view_folders.php'
                    )
                ) {
                    include $file;
                }
            }

            $view->setViewsDir($folders);
        } else {
            $view->setViewsDir(
                [
                    $config->application->siteViewsDir,
                    $config->application->genericViewsDir
                ]
            );
        }
    }
    return $view;
});

/*
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = "Phalcon\\Db\\Adapter\\Pdo\\" . ucwords($config->database->adapter);
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    if (ucwords($config->database->adapter) == 'Postgresql') {
        unset($params['charset']);
    }

    return new $class($params);
});

/*
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
if ($_ENV['APP_ENV'] == 'production') {
    $di->setShared('modelsMetadata', function () {
        $config = $this->getConfig();

        $serializerFactory = new SerializerFactory();
        $adapterFactory    = new CacheAdapter($serializerFactory);
        $options = [
            'host'  => $config->redis->host,
            'port'  => $config->redis->port,
            'index'    => 1,
            'lifetime' => 86400,
            'prefix'   => 'tengu',
        ];

        return new MetaRedis($adapterFactory, $options);
    });
}

/*
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () {
    $config = $this->getConfig();

    $session = new SessionManager();
    $serializerFactory = new SerializerFactory();
    $factory = new AdapterFactory($serializerFactory);
    if ($_ENV['APP_ENV'] == 'production') {
        $options = [
            'host'  => $config->redis->host,
            'port'  => $config->redis->port,
            'index' => '1',
        ];
        $redis = new Redis($factory, $options);
        $session
            ->setAdapter($redis)
            ->start();
    } else {
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );
        $session
            ->setAdapter($files)
            ->setId('tengu')
            ->start();
    }
    return $session;
});
