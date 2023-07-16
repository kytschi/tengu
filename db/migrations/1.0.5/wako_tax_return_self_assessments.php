<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class WakoTaxReturnSelfAssessmentsMigration_105
 */
class WakoTaxReturnSelfAssessmentsMigration_105 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('wako_tax_return_self_assessments', [
            'columns' => [
                new Column(
                    'id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'first' => true
                    ]
                ),
                new Column(
                    'tax_return_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'utr',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'tax_return_id'
                    ]
                ),
                new Column(
                    'your_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'utr'
                    ]
                ),
                new Column(
                    'address',
                    [
                        'type' => Column::TYPE_TINYTEXT,
                        'notNull' => false,
                        'after' => 'your_name'
                    ]
                ),
                new Column(
                    'nino',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'address'
                    ]
                ),
                new Column(
                    'employee_ref',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'nino'
                    ]
                ),
                new Column(
                    'dob',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'employee_ref'
                    ]
                ),
                new Column(
                    'phone',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'dob'
                    ]
                ),
                new Column(
                    'national_insurance',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'phone'
                    ]
                ),
                new Column(
                    'address_date',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'national_insurance'
                    ]
                ),
                new Column(
                    'employment',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'address_date'
                    ]
                ),
                new Column(
                    'employment_page_no',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 11,
                        'after' => 'employment'
                    ]
                ),
                new Column(
                    'self_employment',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'employment_page_no'
                    ]
                ),
                new Column(
                    'self_employment_page_no',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 11,
                        'after' => 'self_employment'
                    ]
                ),
                new Column(
                    'partnership',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'self_employment_page_no'
                    ]
                ),
                new Column(
                    'partnership_page_no',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 11,
                        'after' => 'partnership'
                    ]
                ),
                new Column(
                    'property',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'partnership_page_no'
                    ]
                ),
                new Column(
                    'foreign',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'property'
                    ]
                ),
                new Column(
                    'trusts',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'foreign'
                    ]
                ),
                new Column(
                    'capital_gains',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'trusts'
                    ]
                ),
                new Column(
                    'computation_provided',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'capital_gains'
                    ]
                ),
                new Column(
                    'residence',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'computation_provided'
                    ]
                ),
                new Column(
                    'additional_info',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'residence'
                    ]
                ),
                new Column(
                    'more_pages',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'additional_info'
                    ]
                ),
                new Column(
                    'taxed_uk_interest',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'more_pages'
                    ]
                ),
                new Column(
                    'untaxed_uk_interest',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'taxed_uk_interest'
                    ]
                ),
                new Column(
                    'untaxed_foreign_interest',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'untaxed_uk_interest'
                    ]
                ),
                new Column(
                    'dividends_uk_companies',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'untaxed_foreign_interest'
                    ]
                ),
                new Column(
                    'other_dividends',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'dividends_uk_companies'
                    ]
                ),
                new Column(
                    'foreign_dividends',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'other_dividends'
                    ]
                ),
                new Column(
                    'tax_taken_off_foreign_dividends',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'foreign_dividends'
                    ]
                ),
                new Column(
                    'state_pension',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'tax_taken_off_foreign_dividends'
                    ]
                ),
                new Column(
                    'state_pension_lump',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'state_pension'
                    ]
                ),
                new Column(
                    'tax_off_state_pension_lump',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'state_pension_lump'
                    ]
                ),
                new Column(
                    'other_pensions',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'tax_off_state_pension_lump'
                    ]
                ),
                new Column(
                    'tax_off_other_pensions',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'other_pensions'
                    ]
                ),
                new Column(
                    'tax_benefits',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'tax_off_other_pensions'
                    ]
                ),
                new Column(
                    'tax_taken_off_benefits',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'tax_benefits'
                    ]
                ),
                new Column(
                    'jobseekers_allowance',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'tax_taken_off_benefits'
                    ]
                ),
                new Column(
                    'tax_other_pensions_benefits',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'jobseekers_allowance'
                    ]
                ),
                new Column(
                    'other_taxable_income',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'tax_other_pensions_benefits'
                    ]
                ),
                new Column(
                    'total_amount_allowable_expenses',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'other_taxable_income'
                    ]
                ),
                new Column(
                    'tax_off_other_lump',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'total_amount_allowable_expenses'
                    ]
                ),
                new Column(
                    'benefit_from_pre_assets',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'tax_off_other_lump'
                    ]
                ),
                new Column(
                    'description_of_income',
                    [
                        'type' => Column::TYPE_TINYTEXT,
                        'notNull' => false,
                        'after' => 'benefit_from_pre_assets'
                    ]
                ),
                new Column(
                    'payment_to_registered_pensions',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'description_of_income'
                    ]
                ),
                new Column(
                    'payments_to_retirement_annuity',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'payment_to_registered_pensions'
                    ]
                ),
                new Column(
                    'payments_to_employer_scheme',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'payments_to_retirement_annuity'
                    ]
                ),
                new Column(
                    'payments_to_overseas_pension_scheme',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'payments_to_employer_scheme'
                    ]
                ),
                new Column(
                    'gift_aid_payments',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'payments_to_overseas_pension_scheme'
                    ]
                ),
                new Column(
                    'one_off_payments',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'gift_aid_payments'
                    ]
                ),
                new Column(
                    'gift_aid_payments_treated',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'one_off_payments'
                    ]
                ),
                new Column(
                    'gift_aid_payments_treated_2',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'gift_aid_payments_treated'
                    ]
                ),
                new Column(
                    'qualifying_shares',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'gift_aid_payments_treated_2'
                    ]
                ),
                new Column(
                    'qualifying_land_gifted_charity',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'qualifying_shares'
                    ]
                ),
                new Column(
                    'qualifying_investments',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'qualifying_land_gifted_charity'
                    ]
                ),
                new Column(
                    'gift_aid_payments_non_uk_charity',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'qualifying_investments'
                    ]
                ),
                new Column(
                    'registered_blind',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'gift_aid_payments_non_uk_charity'
                    ]
                ),
                new Column(
                    'registered_blind_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'registered_blind'
                    ]
                ),
                new Column(
                    'surplus_allowance',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'registered_blind_name'
                    ]
                ),
                new Column(
                    'your_surplus_allowance',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'surplus_allowance'
                    ]
                ),
                new Column(
                    'student_loan_repayment',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'your_surplus_allowance'
                    ]
                ),
                new Column(
                    'student_loan_replayment_amount',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'student_loan_repayment'
                    ]
                ),
                new Column(
                    'postgraduate_loan_replayment_amount',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'student_loan_replayment_amount'
                    ]
                ),
                new Column(
                    'child_benefits_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'postgraduate_loan_replayment_amount'
                    ]
                ),
                new Column(
                    'child_benefits_no_children',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 11,
                        'after' => 'child_benefits_amount'
                    ]
                ),
                new Column(
                    'child_benefits_date',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => false,
                        'after' => 'child_benefits_no_children'
                    ]
                ),
                new Column(
                    'convid_incorrectly_claimed',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'child_benefits_date'
                    ]
                ),
                new Column(
                    'convid_incorrectly_claimed_seiss',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'convid_incorrectly_claimed'
                    ]
                ),
                new Column(
                    'spouse_first_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'convid_incorrectly_claimed_seiss'
                    ]
                ),
                new Column(
                    'spouse_last_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'spouse_first_name'
                    ]
                ),
                new Column(
                    'spouse_national_insurance_no',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'spouse_last_name'
                    ]
                ),
                new Column(
                    'spouse_dob',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'spouse_national_insurance_no'
                    ]
                ),
                new Column(
                    'marriage_date',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => false,
                        'after' => 'spouse_dob'
                    ]
                ),
                new Column(
                    'tax_refunded_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'marriage_date'
                    ]
                ),
                new Column(
                    'owe_tax',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'tax_refunded_amount'
                    ]
                ),
                new Column(
                    'owe_tax_on_savings',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'owe_tax'
                    ]
                ),
                new Column(
                    'name_of_bank',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'owe_tax_on_savings'
                    ]
                ),
                new Column(
                    'name_of_account_holder',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'name_of_bank'
                    ]
                ),
                new Column(
                    'sort_code',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'name_of_account_holder'
                    ]
                ),
                new Column(
                    'account_number',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'sort_code'
                    ]
                ),
                new Column(
                    'society_ref',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'account_number'
                    ]
                ),
                new Column(
                    'no_society_account',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'society_ref'
                    ]
                ),
                new Column(
                    'nominees_name',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'no_society_account'
                    ]
                ),
                new Column(
                    'nominee_is_tax_adviser',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'nominees_name'
                    ]
                ),
                new Column(
                    'nominee_address',
                    [
                        'type' => Column::TYPE_TINYTEXT,
                        'notNull' => false,
                        'after' => 'nominee_is_tax_adviser'
                    ]
                ),
                new Column(
                    'nominee_postcode',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'nominee_address'
                    ]
                ),
                new Column(
                    'tax_advisors_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'nominee_postcode'
                    ]
                ),
                new Column(
                    'tax_advisors_phone',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'tax_advisors_name'
                    ]
                ),
                new Column(
                    'tax_advisors_address',
                    [
                        'type' => Column::TYPE_TINYTEXT,
                        'notNull' => false,
                        'after' => 'tax_advisors_phone'
                    ]
                ),
                new Column(
                    'tax_advisors_postcode',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'tax_advisors_address'
                    ]
                ),
                new Column(
                    'tax_advisors_ref',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'tax_advisors_postcode'
                    ]
                ),
                new Column(
                    'other_info',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'tax_advisors_ref'
                    ]
                ),
                new Column(
                    'provisional_figures',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'other_info'
                    ]
                ),
                new Column(
                    'convid_support_received',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'provisional_figures'
                    ]
                ),
                new Column(
                    'additional_pages',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'convid_support_received'
                    ]
                ),
                new Column(
                    'signed_on_behalf',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'additional_pages'
                    ]
                ),
                new Column(
                    'signed_on_behalf_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'signed_on_behalf'
                    ]
                ),
                new Column(
                    'signed_on_behalf_your_address',
                    [
                        'type' => Column::TYPE_TINYTEXT,
                        'notNull' => false,
                        'after' => 'signed_on_behalf_name'
                    ]
                ),
                new Column(
                    'signed_on_behalf_your_postcode',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'signed_on_behalf_your_address'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'signed_on_behalf_your_postcode'
                    ]
                ),
                new Column(
                    'created_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'created_at'
                    ]
                ),
                new Column(
                    'updated_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'created_by'
                    ]
                ),
                new Column(
                    'updated_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'updated_at'
                    ]
                ),
                new Column(
                    'deleted_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => false,
                        'after' => 'updated_by'
                    ]
                ),
                new Column(
                    'deleted_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'deleted_at'
                    ]
                ),
            ],
            'indexes' => [
                new Index('PRIMARY', ['id'], 'PRIMARY'),
                new Index('wako_tax_return_self_assessments_tax_return_id_IDX', ['tax_return_id'], ''),
                new Index('wako_tax_return_self_assessments_created_at_IDX', ['created_at'], ''),
                new Index('wako_tax_return_self_assessments_created_by_IDX', ['created_by'], ''),
                new Index('wako_tax_return_self_assessments_updated_at_IDX', ['updated_at'], ''),
                new Index('wako_tax_return_self_assessments_updated_by_IDX', ['updated_by'], ''),
                new Index('wako_tax_return_self_assessments_deleted_at_IDX', ['deleted_at'], ''),
                new Index('wako_tax_return_self_assessments_deleted_by_IDX', ['deleted_by'], ''),
            ],
            'options' => [
                'TABLE_TYPE' => 'BASE TABLE',
                'AUTO_INCREMENT' => '',
                'ENGINE' => 'InnoDB',
                'TABLE_COLLATION' => 'utf8mb4_general_ci',
            ],
        ]);
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up(): void
    {
    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down(): void
    {
    }
}
