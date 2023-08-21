<?php

/**
 * Tax returns employments model.
 *
 * @package     Kytschi\Wako\Models\TaxReturnEmployments
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

namespace Kytschi\Wako\Models;

use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;

class TaxReturnEmployments extends Model
{
    public $tax_return_id;
    public $utr;
    public $your_name;
    public $payment_amount;
    public $payment_tax_off_amount;
    public $payment_tips_amount;
    public $paye_tax_ref;
    public $employer_name;
    public $director;
    public $director_ceased_date;
    public $company_closed;
    public $off_payroll;
    public $company_cars;
    public $fuel_for_company_cars;
    public $private_medical_insurance;
    public $vouchers_amount;
    public $goods_amount;
    public $accommodation_amount;
    public $other_benefits;
    public $expenses_payments;
    public $business_travel_amount;
    public $fixed_deductions_amount;
    public $professional_fees;
    public $other_expenses_amount;

    protected $encrypted = [
        'paye_tax_ref',
    ];
    
    public function initialize()
    {
        $this->setSource('wako_tax_return_employments');

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
                'alias'    => 'file',
                'reusable' => false,
                'params' => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );
    }
}
