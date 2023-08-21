<?php

/**
 * Settings model.
 *
 * @package     Kytschi\Phoenix\Models\Settings
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright Kytschi- All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Phoenix\Models;

use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Users;

class Settings extends Model
{
    public $vat = 25.00;
    public $zero_stock;
    public $onscreen_keyboard;
    public $default_shipping;
    
    public function initialize()
    {
        $this->setSource('phoenix_settings');
    }

    public function getLogs()
    {
        return (new Logs())->find([
            'conditions' => 'resource = "sales-settings"'
        ]);
    }
}
