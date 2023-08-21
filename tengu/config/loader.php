<?php

/**
 * Looder.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301, USA.
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
