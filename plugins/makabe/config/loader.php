<?php

/**
 * Looder.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

$makabe_namespaces = [
    'Kytschi\Makabe\Controllers' => $config->application->pluginsDir . 'makabe/controllers',
    'Kytschi\Makabe\Exceptions' => $config->application->pluginsDir . 'makabe/exceptions',
    'Kytschi\Makabe\Models' => $config->application->pluginsDir . 'makabe/models',
    'Kytschi\Makabe\Traits' => $config->application->pluginsDir . 'makabe/traits',
    'Kytschi\Makabe\Jobs' => $config->application->pluginsDir . 'makabe/jobs'
];

$namespaces = array_merge($makabe_namespaces, $namespaces);
