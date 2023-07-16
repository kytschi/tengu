<?php

/**
 * Tengu cron.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright 2022 Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

include 'Cron.php';

use Kytschi\Tengu\Controllers\Core\QueueController;

(new QueueController())->trigger();

foreach ($config->apps as $app => $status) {
    if (file_exists($file = $config->application->pluginsDir . $app . '/crons/' . ucwords($app) . '.php')) {
        include $file;
    }
}
