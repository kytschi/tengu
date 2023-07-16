<?php

/**
 * Generic shipping company model.
 *
 * @package     Kytschi\Phoenix\Models\ShippingCompanies\Model
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
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
