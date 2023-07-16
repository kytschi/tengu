<?php

/**
 * Migrations config.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

use Phalcon\Config;

include './vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

return new Config([
    'database' => [
        'adapter'     => $_ENV['DB_CONNECTION'],
        'host'        => $_ENV['DB_HOST'],
        'port'        => $_ENV['DB_PORT'],
        'username'    => $_ENV['DB_USERNAME'],
        'password'    => $_ENV['DB_PASSWORD'],
        'dbname'      => $_ENV['DB_DATABASE'],
        'charset' => 'utf8',
    ],
    'application' => [
        'logInDb' => true,
        'migrationsDir' =>  __DIR__ . '/db/migrations',
        'migrationsTsBased' => false,
        'exportDataFromTables' => [
            // Tables names
            // Attention! It will export data every new migration
        ],
    ],
]);
