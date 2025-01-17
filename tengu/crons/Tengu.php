<?php

/**
 * Tengu cron.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
 */

include 'Cron.php';

use Kytschi\Tengu\Controllers\Core\QueueController;

(new QueueController())->trigger();

foreach ($config->apps as $app => $status) {
    if (file_exists($file = $config->application->pluginsDir . $app . '/crons/' . ucwords($app) . '.php')) {
        include $file;
    }
}
