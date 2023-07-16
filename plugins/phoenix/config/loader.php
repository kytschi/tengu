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

$phoenix_namespaces = [
    'Kytschi\Phoenix\Controllers' => $config->application->pluginsDir . 'phoenix/controllers',
    'Kytschi\Phoenix\Exceptions' => $config->application->pluginsDir . 'phoenix/exceptions',
    'Kytschi\Phoenix\Models' => $config->application->pluginsDir . 'phoenix/models',
    'Kytschi\Phoenix\Models\ShippingCompanies' =>   $config->application->pluginsDir .
                                                    'phoenix/models/shippingcompanies'
];

$namespaces = array_merge($phoenix_namespaces, $namespaces);
