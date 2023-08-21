<?php

/**
 * Fix the emails script.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2022 Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

include __DIR__ . '/../crons/Cron.php';

use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Models\Core\Users;

$users = Users::find();

echo "Fixing emails\n";
foreach ($users as $user) {
    echo '.';
    if (strpos($user->email, '@') !== false) {
        echo StringHelper::encrypt($user->email);
        if ($user->update() === false) {
            'Failed to update the user, ' . $user->getMessages();
        }
    }
}

echo "\nComplete\n";
