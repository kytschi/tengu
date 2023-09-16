<?php

/**
 * Loader.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

$phoenix_namespaces = [
    'Kytschi\Phoenix\Controllers' => $config->application->pluginsDir . 'phoenix/controllers',
    'Kytschi\Phoenix\Exceptions' => $config->application->pluginsDir . 'phoenix/exceptions',
    'Kytschi\Phoenix\Models' => $config->application->pluginsDir . 'phoenix/models',
    'Kytschi\Phoenix\Models\ShippingCompanies' => $config->application->pluginsDir .
                                                    'phoenix/models/shippingcompanies',
    'Kytschi\Phoenix\Traits' => $config->application->pluginsDir . 'phoenix/traits',
];

$namespaces = array_merge($phoenix_namespaces, $namespaces);
