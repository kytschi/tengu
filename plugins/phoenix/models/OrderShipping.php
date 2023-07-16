<?php

/**
 * Orders model.
 *
 * @package     Kytschi\Phoenix\Models\OrderShipping
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Phoenix\Models;

use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;

class OrderShipping extends Model
{
    public $order_id;
    public $shipping_company_code;
    public $shipping_option;
    public $weight = 0.00;
    public $width = 0.00;
    public $height = 0.00;
    public $length = 0.00;
    public $shipping_id;
    public $label;
    public $tracking_code;
    public $shipping_charge = 0.00;
    
    public function initialize()
    {
        $this->setSource('phoenix_order_shipping');

        $this->hasOne(
            'updated_by',
            Users::class,
            'id',
            [
                'alias'    => 'updated',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'created_by',
            Users::class,
            'id',
            [
                'alias'    => 'created',
                'reusable' => true
            ]
        );
        
        $this->hasOne(
            'deleted_by',
            Users::class,
            'id',
            [
                'alias'    => 'deleted',
                'reusable' => true
            ]
        );
    }
}
