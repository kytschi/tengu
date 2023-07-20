<?php

/**
 * Fix the encrypted data script.
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

include __DIR__ . '/../crons/Cron.php';

use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Users;

function decrypt($string)
{
    return openssl_decrypt(
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
