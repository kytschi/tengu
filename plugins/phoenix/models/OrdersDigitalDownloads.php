<?php

/**
 * Orders Digital Downloads model.
 *
 * @package     Kytschi\Phoenix\Models\OrdersDigitalDownloads
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
 */

namespace Kytschi\Phoenix\Models;

use Kytschi\Phoenix\Models\Orders;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;

class OrdersDigitalDownloads extends Model
{
    public $order_id;
    public $download_url;
    public $downloads = 0;
    public $download_limit = 0;

    public function initialize()
    {
        $this->setSource('phoenix_orders_digital_downloads');

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
            'order_id',
            Orders::class,
            'id',
            [
                'alias'    => 'order',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );
    }
}
