<?php

/**
 * Inital install.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

include __DIR__ . '/../crons/Cron.php';

use Kytschi\Phoenix\Models\PaymentGateway;
use Kytschi\Tengu\Controllers\Core\GroupsController;
use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Website\Settings;

echo " Setting up Tengu\n";

$settings = (new Settings())->findFirst(
    [
        'reusable' => true,
        'limit' => '1'
    ]
);

if (empty($settings)) {
    $model = new Settings();
    $model->name = 'My website';
    $model->status = 'offline';
    $model->tengu_theme = 'default';

    if ($model->save() === false) {
        echo 'Failed to install, ' . implode(", ", $model->getMessages());
        die();
    }
    echo " Init settings installed\n";
}

if (!file_exists(__DIR__ . '/../../secure/tengu.pub')) {
    printf("\e[0;31m Please create the 'tengu.pub' file, see README on how to create this.\e[0m\n");
    die();
}

$users = (new Users())->findFirst(
    [
        'conditions' => 'deleted_at IS NULL',
        'reusable' => true,
        'limit' => '1'
    ]
);

if (empty($users)) {
    $controller = new GroupsController();
    $model = new Users();
    $model->group_id = $controller::$system['Super user'][0];
    $model->email = readline(' Please enter your email for login: ');
    $password = readline(' Please enter your password for login: ');
    $model->password = StringHelper::hash($password);
    $model->first_name = readline(' Please enter your first name: ');
    $model->last_name = readline(' Please enter your last name: ');
    $model->type = 'user';
    $model->status = 'active';

    if ($model->save() === false) {
        echo 'Failed to install, ' . implode(", ", $model->getMessages());
        die();
    }

    echo " Go to your website and add /tengu to the URL to login\n";
}

$gateways = ['Paypal' => 'Paypal', 'Stripe' => 'Credit/Debit cards'];
foreach ($gateways as $name => $description) {
    $payment = PaymentGateway::findFirst(
        [
            'conditions' => 'name=:name:',
            'bind' => ['name' => $name],
            'reusable' => true,
        ]
    );
    if (!empty($payment)) {
        continue;
    }

    $model = new PaymentGateway([
        'name' => $name,
        'description' => $description,
        'created_at' => date('Y-m-d H:i:s'),
        'created_by' => '00000000-0000-0000-0000-000000000000',
        'updated_at' => date('Y-m-d H:i:s'),
        'updated_by' => '00000000-0000-0000-0000-000000000000'
    ]);

    if ($model->save() === false) {
        echo 'Failed to install, ' . implode(", ", $model->getMessages());
        die();
    }
}

if (!file_exists(__DIR__ . '/../../plugins/geolite2/GeoLite2-City.mmdb')) {
    $unzip = new \ZipArchive();
    $file = $unzip->open(__DIR__ . '/../../plugins/geolite2/GeoLite2-City.zip');
    if ($file === true) {
        $unzip->extractTo(__DIR__ . '/../../plugins/geolite2/');
        $unzip->close();
    } else {
        echo 'Failed to extract the GeoLite2 DB from the zip';
    }
}
