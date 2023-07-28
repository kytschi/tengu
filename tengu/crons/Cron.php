<?php

/**
 * Cron global.
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
