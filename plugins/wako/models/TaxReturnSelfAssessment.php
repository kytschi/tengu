<?php

/**
 * Tax returns model.
 *
 * @package     Kytschi\Wako\Models\TaxReturnSelfAssessment
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

class TaxReturnSelfAssessment extends Model
{
    public $tax_return_id;
    public $utr;
    public $nino;
    public $employee_ref;
    public $your_name;
    public $address;
    public $dob;
    public $phone;
    public $national_insurance;
    public $address_date;
    public $employment;
    public $employment_page_no;
    public $self_employment;
    public $self_employment_page_no;
    public $partnership;
    public $partnership_page_no;
    public $property;
    public $foreign;
    public $trusts;
    public $capital_gains;
    public $computation_provided;
    public $residence;
    public $additional_info;
    public $more_pages;
    public $taxed_uk_interest;
    public $untaxed_uk_interest;
    public $untaxed_foreign_interest;
    public $dividends_uk_companies;
    public $other_dividends;
    public $foreign_dividends;
    public $tax_taken_off_foreign_dividends;
    public $state_pension;
    public $state_pension_lump;
    public $tax_off_state_pension_lump;
    public $other_pensions;
    public $tax_off_other_pensions;
    public $tax_benefits;
    public $tax_taken_off_benefits;
    public $jobseekers_allowance;
    public $tax_other_pensions_benefits;
    public $other_taxable_income;
    public $total_amount_allowable_expenses;
    public $tax_off_other_lump;
    public $benefit_from_pre_assets;
    public $description_of_income;
    public $payment_to_registered_pensions;
    public $payments_to_retirement_annuity;
    public $payments_to_employer_scheme;
    public $payments_to_overseas_pension_scheme;
    public $gift_aid_payments;
    public $one_off_payments;
    public $gift_aid_payments_treated;
    public $gift_aid_payments_treated_2;
    public $qualifying_shares;
    public $qualifying_land_gifted_charity;
    public $qualifying_investments;
    public $gift_aid_payments_non_uk_charity;
    public $registered_blind;
    public $registered_blind_name;
    public $spouse_surplus_allowance;
    public $your_surplus_allowance;
    public $student_loan_repayment;
    public $student_loan_replayment_amount;
    public $postgraduate_loan_replayment_amount;
    public $child_benefits_amount;
    public $child_benefits_no_children;
    public $child_benefits_date;
    public $convid_incorrectly_claimed;
    public $convid_incorrectly_claimed_seiss;
    public $spouse_first_name;
    public $spouse_last_name;
    public $spouse_national_insurance_no;
    public $spouse_dob;
    public $marriage_date;
    public $tax_refunded_amount;
    public $owe_tax;
    public $owe_tax_on_savings;
    public $name_of_bank;
    public $name_of_account_holder;
    public $sort_code;
    public $account_number;
    public $society_ref;
    public $no_society_account;
    public $nominees_name;
    public $nominee_is_tax_adviser;
    public $nominee_address;
    public $nominee_postcode;
    public $tax_advisors_name;
    public $tax_advisors_phone;
    public $tax_advisors_address;
    public $tax_advisors_postcode;
    public $tax_advisors_ref;
    public $other_info;
    public $provisional_figures;
    public $convid_support_received;
    public $additional_pages;
    public $signed_on_behalf;
    public $signed_on_behalf_name;
    public $signed_on_behalf_your_name;
    public $signed_on_behalf_your_address;
    public $signed_on_behalf_your_postcode;

    protected $encrypted = [
        'utr',
        'nino',
        'employee_ref',
        'your_name',
        'address',
        'dob',
        'phone',
        'national_insurance',
        'spouse_first_name',
        'spouse_last_name',
        'spouse_national_insurance_no',
        'spouse_dob',
        'name_of_bank',
        'name_of_account_holder',
        'sort_code',
        'account_number',
        'society_ref',
        'nominees_name',
        'nominee_address',
        'nominee_postcode',
        'tax_advisors_name',
        'tax_advisors_phone',
        'tax_advisors_address',
        'tax_advisors_postcode',
        'tax_advisors_ref',
        'other_info',
        'signed_on_behalf_name',
        'signed_on_behalf_your_name',
        'signed_on_behalf_your_address',
        'signed_on_behalf_your_postcode'
    ];
    
    public function initialize()
    {
        $this->setSource('wako_tax_return_self_assessments');

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
