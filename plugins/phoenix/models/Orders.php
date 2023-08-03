<?php

/**
 * Orders model.
 *
 * @package     Kytschi\Phoenix\Models\Orders
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Phoenix\Models;

use Kytschi\Phoenix\Models\OrderAddresses;
use Kytschi\Phoenix\Models\OrderItems;
use Kytschi\Phoenix\Models\OrderShipping;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;

class Orders extends Model
{
    public $number;
    public $customer_id;
    public $sub_total = 0.00;
    public $vat = 0.00;
    public $total = 0.00;
    public $quantity = 0;
    public $status = 'basket';

    public function initialize()
    {
        $this->setSource('phoenix_orders');

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
            OrderItems::class,
            'order_id',
            [
                'alias'    => 'items',
                'reusable' => false,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'created_at ASC'
                ]
            ]
        );

        $this->hasOne(
            'id',
            OrderAddresses::class,
            'order_id',
            [
                'alias'    => 'billing',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL and type="billing"'
                ]
            ]
        );

        $this->hasOne(
            'id',
            OrderAddresses::class,
            'order_id',
            [
                'alias'    => 'delivery',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL and type="delivery"'
                ]
            ]
        );

        $this->hasOne(
            'id',
            OrderShipping::class,
            'order_id',
            [
                'alias'    => 'shipping',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Tags::class,
            'resource_id',
            [
                'alias'    => 'tags',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL AND resource="order" AND type IS NULL',
                    'order' => 'tag'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Logs::class,
            'resource_id',
            [
                'alias'    => 'logs',
                'reusable' => false,
                'params'   => [
                    'order' => 'created_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Notes::class,
            'resource_id',
            [
                'alias'    => 'notes',
                'reusable' => false,
                'params'   => [
                    'order' => 'created_at DESC'
                ]
            ]
        );
    }

    public function getName()
    {
        $name = 'Order #' . $this->number;
        return $name;
    }
}
