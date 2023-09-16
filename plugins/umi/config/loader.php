<?php

/**
 * Looder.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

$umi_namespaces = [
    'Kytschi\Umi\Controllers' => $config->application->pluginsDir . 'umi/controllers',
    'Kytschi\Umi\Models' => $config->application->pluginsDir . 'umi/models'
];

$namespaces = array_merge($umi_namespaces, $namespaces);
