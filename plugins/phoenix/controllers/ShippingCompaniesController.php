<?php

/**
 * Shipping companies controller.
 *
 * @package     Kytschi\Phoenix\Controllers\ShippingCompaniesController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Phoenix\Models\ShippingCompanies\DPDUK;
use Kytschi\Phoenix\Models\ShippingCompanies\EvriUK;
use Phalcon\Mvc\Controller;

class ShippingCompaniesController extends Controller
{
    public function get()
    {
        $companies[] = self::setDefault(new EvriUK());
        $companies[] = self::setDefault(new DPDUK());

        return $companies;
    }

    public function setDefault($model)
    {
        if ($this->tengu->settings->sales) {
            if ($this->tengu->settings->sales->default_shipping == $model->code) {
                $model->default = true;
            }
        }
        return $model;
    }
}
