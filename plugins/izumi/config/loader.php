<?php

/**
 * Loader.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

$izumi_namespaces = [
    'Kytschi\Izumi\Controllers' => $config->application->pluginsDir . 'izumi/controllers',
    'Kytschi\Izumi\Models' => $config->application->pluginsDir . 'izumi/models',
    'Kytschi\Izumi\Traits' => $config->application->pluginsDir . 'izumi/traits'
];

$namespaces = array_merge($izumi_namespaces, $namespaces);
