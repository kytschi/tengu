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

$izumi_namespaces = [
    'Kytschi\Izumi\Controllers' => $config->application->pluginsDir . 'izumi/controllers'
];

$namespaces = array_merge($izumi_namespaces, $namespaces);
