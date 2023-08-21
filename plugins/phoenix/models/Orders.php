<?php

/**
 * Orders model.
 *
 * @package     Kytschi\Phoenix\Models\Orders
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
