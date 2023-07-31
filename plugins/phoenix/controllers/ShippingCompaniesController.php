<?php

/**
 * Shipping companies controller.
 *
 * @package     Kytschi\Phoenix\Controllers\ShippingCompaniesController
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
