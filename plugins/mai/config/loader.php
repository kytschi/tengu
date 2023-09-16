<?php

/**
 * Loader.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

$wako_namespaces = [
    'Kytschi\Mai\Controllers' => $config->application->pluginsDir . 'mai/controllers',
    'Kytschi\Mai\Models' => $config->application->pluginsDir . 'mai/models'
];

$namespaces = array_merge($wako_namespaces, $namespaces);
