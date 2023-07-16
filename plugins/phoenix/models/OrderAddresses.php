<?php

/**
 * Order addresses model.
 *
 * @package     Kytschi\Phoenix\Models\OrderAddresses
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Phoenix\Models;

use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;

class OrderAddresses extends Model
{
    public $order_id;
    public $type = 'billing';
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $company;
    public $address_line_1;
    public $address_line_2;
    public $town;
    public $county;
    public $country;
    public $postcode;

    protected $encrypted = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'town',
        'county',
        'country',
        'postcode'
    ];
    
    public function initialize()
    {
        $this->setSource('phoenix_order_addresses');

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
    
    public function getFull()
    {
        $return = '';
        if ($this->company) {
            $return .= $this->company . ', ';
        }

        $return .= $this->first_name . ' ' . $this->last_name . ', ';
        $return .= $this->address_line_1 . ', ';
        if (!empty($this->address_line_2)) {
            $return .= $this->address_line_2 . ', ';
        }
        $return .= $this->town . ', ';
        $return .= $this->county . ', ';
        $return .= $this->postcode . ', ';
        $return .= $this->country;

        return rtrim($return, ', ');
    }

    public function getSummary()
    {
        $return = '';
        if ($this->company) {
            $return .= $this->company . ', ';
        }

        $return .= $this->address_line_1 . ', ';
        $return .= $this->postcode . ', ';

        return rtrim($return, ', ');
    }
}
