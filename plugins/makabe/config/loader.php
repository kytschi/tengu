<?php

/**
 * Looder.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

$makabe_namespaces = [
    'Kytschi\Makabe\Controllers' => $config->application->pluginsDir . 'makabe/controllers',
    'Kytschi\Makabe\Exceptions' => $config->application->pluginsDir . 'makabe/exceptions',
    'Kytschi\Makabe\Models' => $config->application->pluginsDir . 'makabe/models',
    'Kytschi\Makabe\Traits' => $config->application->pluginsDir . 'makabe/traits',
    'Kytschi\Makabe\Jobs' => $config->application->pluginsDir . 'makabe/jobs'
];

$namespaces = array_merge($makabe_namespaces, $namespaces);
