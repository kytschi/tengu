<?php

/**
 * Fix the encrypted data script.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

include __DIR__ . '/../crons/Cron.php';

use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Users;

function decrypt($string)
{
    return @openssl_decrypt(
        $string,
        'aes128',
        $_ENV['APP_KEY']
    );
}

echo "Fixing users\n";
$entries = Users::find();
foreach ($entries as $entry) {
    $entry->email = decrypt($entry->email);
    $entry->first_name = StringHelper::encrypt($entry->first_name);
    $entry->last_name = StringHelper::encrypt($entry->last_name);
    if ($entry->update() === false) {
        'Failed to update the user, ' . $entry->getMessages();
    }
}

echo "Fixing files\n";
$entries = Files::find(['condition' => 'compress=1']);
foreach ($entries as $entry) {
    if ($file = decrypt(file_get_contents(BASE_PATH . '/public/dump/' . $entry->filename))) {
        file_put_contents(
            BASE_PATH . '/public/dump/' . $entry->filename,
            StringHelper::encrypt($file)
        );
    }
}

echo "\nComplete\n";
