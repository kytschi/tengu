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

$umi_namespaces = [
    'Kytschi\Umi\Controllers' => $config->application->pluginsDir . 'umi/controllers',
    'Kytschi\Umi\Models' => $config->application->pluginsDir . 'umi/models'
];

$namespaces = array_merge($umi_namespaces, $namespaces);
