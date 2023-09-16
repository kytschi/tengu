<?php

/**
 * Looder.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

$loader = new \Phalcon\Autoload\Loader();

$namespaces = [
    'Kytschi\Tengu\Controllers' => $config->application->controllersDir,
    'Kytschi\Tengu\Controllers\Core' => $config->application->controllersDir . '/core/',
    'Kytschi\Tengu\Controllers\Website' => $config->application->controllersDir . '/website/',
    'Kytschi\Tengu\Exceptions' => $config->application->appDir . '/exceptions/',
    'Kytschi\Tengu\Helpers' => $config->application->appDir . '/helpers/',
    'Kytschi\Tengu\Models' => $config->application->modelsDir,
    'Kytschi\Tengu\Models\Core' => $config->application->modelsDir . '/core/',
    'Kytschi\Tengu\Models\Website' => $config->application->modelsDir . '/website/',
    'Kytschi\Tengu\Src' => $config->application->srcDir,
    'Kytschi\Tengu\Traits' => $config->application->traitsDir,
    'Kytschi\Tengu\Traits\Core' => $config->application->traitsDir . '/core/',
    'Kytschi\Tengu\Traits\Website' => $config->application->traitsDir . '/website/',
    'Kytschi\Tengu\Jobs' => $config->application->jobsDir . '/',
];

foreach ($config->apps as $app => $status) {
    if (file_exists($file = $config->application->pluginsDir . $app . '/config/loader.php')) {
        include $file;
    }
}

$loader->setNamespaces($namespaces);

$loader->register();
