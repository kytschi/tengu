<?php

/**
 * Generic shipping option model.
 *
 * @package     Kytschi\Phoenix\Models\ShippingCompanies\Option
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Phoenix\Models\ShippingCompanies;

class Option
{
    public $code;
    public $name;
    public $price_drop_off;
    public $price_collection;
    public $weight_from;
    public $weight_to;
    public $postable = true;
    public $width;
    public $height;
    public $length;

    public function __construct(array $args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
