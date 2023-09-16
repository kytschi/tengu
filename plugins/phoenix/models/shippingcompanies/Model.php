<?php

/**
 * Generic shipping company model.
 *
 * @package     Kytschi\Phoenix\Models\ShippingCompanies\Model
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Phoenix\Models\ShippingCompanies;

class Model
{
    public $code;
    public $name;
    public $logo;
    public $options = [];
    public $default = false;
}
