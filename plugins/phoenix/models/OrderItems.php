<?php

/**
 * Order items model.
 *
 * @package     Kytschi\Phoenix\Models\OrderItems
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Phoenix\Models;

use Kytschi\Phoenix\Models\Products;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;

class OrderItems extends Model
{
    public $order_id;
    public $product_id;
    public $quanity;
    public $fulfilled;
    public $price;
    
    public function initialize()
    {
        $this->setSource('phoenix_order_items');

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

        $this->hasOne(
            'product_id',
            Products::class,
            'id',
            [
                'alias'    => 'product',
                'reusable' => true
            ]
        );
    }
}
