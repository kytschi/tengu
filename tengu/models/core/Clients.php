<?php

/**
 * Clients model.
 *
 * @package     Kytschi\Tengu\Models\Core\Clients
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Models\Core;

use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;

class Clients extends Model
{
    public $name;
    public $billing_address_line_1;
    public $billing_address_line_2;
    public $billing_city;
    public $billing_county;
    public $billing_country;
    public $billing_postcode;
    public $billing_fullname;
    public $billing_phone_number;
    public $billing_email;
    public $shipping_address_line_1;
    public $shipping_address_line_2;
    public $shipping_city;
    public $shipping_county;
    public $shipping_country;
    public $shipping_postcode;
    public $shipping_fullname;
    public $shipping_phone_number;
    public $shipping_email;
    public $status = 'active';

    public function initialize()
    {
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
            'id',
            Files::class,
            'resource_id',
            [
                'alias'    => 'logo',
                'reusable' => true,
                'params' => [
                    'conditions' => 'resource = "client-logo" AND deleted_at IS NULL'
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
                    'conditions' => 'deleted_at IS NULL',
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
                'reusable' => true,
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
                'reusable' => true,
                'params'   => [
                    'order' => 'created_at DESC'
                ]
            ]
        );
    }
}
