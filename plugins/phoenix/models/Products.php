<?php

/**
 * Products model.
 *
 * @package     Kytschi\Phoenix\Models\Products
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Phoenix\Models;

use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Pages;

class Products extends Model
{
    public $page_id;
    public $type;
    public $code;
    public $price;
    public $stock;
    public $low_stock;
    public $vat;
    public $requires_shipping = 1;

    public function initialize()
    {
        $this->setSource('phoenix_products');

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
            'page_id',
            Pages::class,
            'id',
            [
                'alias'    => 'page',
                'reusable' => true
            ]
        );
    }

    public function getName()
    {
        return !empty($this->page) ? $this->page->name : '';
    }
}
