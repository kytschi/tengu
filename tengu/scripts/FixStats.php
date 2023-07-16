<?php

/**
 * Fix the stats script.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright 2022 Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

include __DIR__ . '/../crons/Cron.php';

use Kytschi\Tengu\Controllers\IndexController;
use Kytschi\Tengu\Models\Website\Stats;

$controller = new IndexController();

echo "Cleaning bots\n";
$stats = Stats::find(['conditions' => 'bot IS NOT NULL']);
foreach ($stats as $stat) {
    echo '.';
    if (in_array($stat->bot, $controller->exclude_bots)) {
        $stat->bot = null;
        if ($stat->update() === false) {
            echo 'Failed to save the stat entry';
            die();
        }
        continue;
    }
}
echo "\nComplete\n";

echo "Fixing stats\n";
$stats = Stats::find(['conditions' => 'bot IS NULL']);
foreach ($stats as $stat) {
    echo '.';

    $stat->bot = null;
    foreach ($controller->bots as $type => $check) {
        if (strpos(strtolower($stat->agent), strtolower($check)) !== false) {
            $stat->bot = $type;
            break;
        }
    }

    $stat->browser = null;
    $browser_obj = @get_browser($stat->agent);
    if (!empty($browser_obj)) {
        foreach ($controller->browsers as $key => $check) {
            if (
                strpos(strtolower($browser_obj->browser), str_replace(' ', '', strtolower($check))) !== false
            ) {
                $stat->browser = is_string($key) ? $key : $check;
                break;
            }
        }
    }

    $stat->operating_system = null;
    foreach ($controller->operating_systems as $type => $check) {
        if (
            strpos(str_replace(' ', '', strtolower($stat->agent)), str_replace(' ', '', strtolower($check))) !== false
        ) {
            $stat->operating_system = $type;
            break;
        }
    }

    if ($stat->update() === false) {
        echo 'Failed to save the stat entry';
        die();
    }
}

echo "\nComplete\n";
