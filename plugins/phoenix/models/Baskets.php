<?php

/**
 * Baskets model.
 *
 * @package     Kytschi\Phoenix\Models\Baskets
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Phoenix\Models;

use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Phoenix\Models\BasketItems;

class Baskets extends Model
{
    public $sub_total = 0.00;
    public $vat = 0.00;
    public $total = 0.00;
    public $quantity = 1;
    public $status = 'active';
    
    public function initialize()
    {
        $this->setSource('phoenix_baskets');

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

        $this->hasMany(
            'id',
            BasketItems::class,
            'basket_id',
            [
                'alias'    => 'items',
                'reusable' => false,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'created_at ASC'
                ]
            ]
        );
    }
}
