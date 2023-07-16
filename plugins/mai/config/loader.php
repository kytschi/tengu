<?php

/**
 * Loader.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

$wako_namespaces = [
    'Kytschi\Mai\Controllers' => $config->application->pluginsDir . 'mai/controllers',
    'Kytschi\Mai\Models' => $config->application->pluginsDir . 'mai/models'
];

$namespaces = array_merge($wako_namespaces, $namespaces);
