<?php

/**
 * Fix the thumbnails script.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

include __DIR__ . '/../crons/Cron.php';

use Kytschi\Tengu\Controllers\Core\FilesController;
use Kytschi\Tengu\Models\Core\Files;

$files = Files::find(
    [
        'conditions' => 'deleted_at IS NULL'
    ]
);

$controller = new FilesController();


echo "Creating thumbnails\n";
foreach ($files as $file) {
    if (file_exists(BASE_PATH . '/public/dump/thumb-' . $file->filename)) {
        continue;
    }

    $tmp = [
        'tmp_name' => BASE_PATH . '/public/dump/' . $file->filename,
        'type' => $file->mime_type
    ];

    echo "-";
    $controller->createThumb($file, $tmp, BASE_PATH . '/public/dump/');
}

echo "\nComplete";
