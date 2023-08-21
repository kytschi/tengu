<?php

/**
 * Settings model.
 *
 * @package     Kytschi\Wako\Models\Settings
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright Kytschi- All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Wako\Models;

use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Users;

class Settings extends Model
{
    public $registered_company_name;
    public $registered_company_address;
    public $registered_company_number;
    public $shares;
    public $secretary_id;
    public $currency;
    public $paye_tax_ref_number;

    protected $encrypted = [
        'paye_tax_ref_number'
    ];
    
    public function initialize()
    {
        $this->setSource('wako_settings');

        $this->hasOne(
            'secretary_id',
            Users::class,
            'id',
            [
                'alias'    => 'secretary',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'id',
            Files::class,
            'resource_id',
            [
                'alias'    => 'secretary_signature',
                'reusable' => true,
                'params' => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );
    }

    public function getLogs()
    {
        return (new Logs())->find([
            'conditions' => 'resource = "finance-settings"'
        ]);
    }
}
