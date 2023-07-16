<?php

/**
 * Shipping companies controller.
 *
 * @package     Kytschi\Phoenix\Controllers\ShippingCompaniesController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
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
        if ($this->tengu->settings->sales->default_shipping == $model->code) {
            $model->default = true;
        }
        return $model;
    }
}
