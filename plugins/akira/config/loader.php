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

$akira_namespaces = [
    'Kytschi\Akira\Controllers' => $config->application->pluginsDir . 'akira/controllers'
];

$namespaces = array_merge($akira_namespaces, $namespaces);
