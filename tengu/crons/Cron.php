<?php

/**
 * Cron global.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright 2022 Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

use Phalcon\Di\FactoryDefault;

define('BASE_PATH', dirname(__DIR__) . '/..');
define('APP_PATH', BASE_PATH . '/tengu/');

try {
    /**
     * Load the autoloader from composer.
     */
    include BASE_PATH . '/vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();

    if (!empty($_SERVER['REQUEST_URI'])) {
        if (
            substr(
                $_SERVER['REQUEST_URI'],
                0,
                strlen($_ENV['APP_TENGU_URL'])
            ) == $_ENV['APP_TENGU_URL']
        ) {
            define('TENGU_BACKEND', true);
        } else {
            define('TENGU_BACKEND', false);
        }
    } else {
        define('TENGU_BACKEND', false);
    }

    /**
     * The FactoryDefault Dependency Injector automatically registers
     * the services that provide a full stack framework.
     */
    $di = new FactoryDefault();

    /**
     * Read services
     */
    include APP_PATH . '/config/services.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';
} catch (\Exception $err) {
    echo $err->getMessage() . "\n";
    die();
}
