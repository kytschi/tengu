<?php

/**
 * Loader.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

$akira_namespaces = [
    'Kytschi\Akira\Controllers' => $config->application->pluginsDir . 'akira/controllers',
    'Kytschi\Akira\Models' => $config->application->pluginsDir . 'akira/models',
    'Kytschi\Akira\Traits' => $config->application->pluginsDir . 'akira/traits'
];

$namespaces = array_merge($akira_namespaces, $namespaces);
