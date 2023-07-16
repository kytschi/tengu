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
    'Kytschi\Wako\Controllers' => $config->application->pluginsDir . 'wako/controllers',
    'Kytschi\Wako\Exceptions' => $config->application->pluginsDir . 'wako/exceptions',
    'Kytschi\Wako\Models' => $config->application->pluginsDir . 'wako/models',
    'Kytschi\Wako\Parsers' => $config->application->pluginsDir . 'wako/parsers',
    'Kytschi\Wako\PDFs' => $config->application->pluginsDir . 'wako/pdfs',
    'Kytschi\Wako\Traits' => $config->application->pluginsDir . 'wako/traits'
];

$namespaces = array_merge($wako_namespaces, $namespaces);
