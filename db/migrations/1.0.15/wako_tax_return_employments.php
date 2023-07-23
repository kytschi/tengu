<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class WakoTaxReturnEmploymentsMigration_115
 */
class WakoTaxReturnEmploymentsMigration_115 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('wako_tax_return_employments', [
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
                    'payment_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'your_name'
                    ]
                ),
                new Column(
                    'payment_tax_off_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'payment_amount'
                    ]
                ),
                new Column(
                    'payment_tips_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'payment_tax_off_amount'
                    ]
                ),
                new Column(
                    'paye_tax_ref',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'payment_tips_amount'
                    ]
                ),
                new Column(
                    'employer_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'paye_tax_ref'
                    ]
                ),
                new Column(
                    'director',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'employer_name'
                    ]
                ),
                new Column(
                    'director_ceased_date',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => false,
                        'after' => 'director'
                    ]
                ),
                new Column(
                    'company_closed',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'director_ceased_date'
                    ]
                ),
                new Column(
                    'off_payroll',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'company_closed'
                    ]
                ),
                new Column(
                    'company_cars',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'off_payroll'
                    ]
                ),
                new Column(
                    'fuel_for_company_cars',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'company_cars'
                    ]
                ),
                new Column(
                    'private_medical_insurance',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'fuel_for_company_cars'
                    ]
                ),
                new Column(
                    'vouchers_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'private_medical_insurance'
                    ]
                ),
                new Column(
                    'goods_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'vouchers_amount'
                    ]
                ),
                new Column(
                    'accommodation_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'goods_amount'
                    ]
                ),
                new Column(
                    'other_benefits',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'accommodation_amount'
                    ]
                ),
                new Column(
                    'expenses_payments',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'other_benefits'
                    ]
                ),
                new Column(
                    'business_travel_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'expenses_payments'
                    ]
                ),
                new Column(
                    'fixed_deductions_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'business_travel_amount'
                    ]
                ),
                new Column(
                    'professional_fees',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'fixed_deductions_amount'
                    ]
                ),
                new Column(
                    'other_expenses_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'professional_fees'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'other_expenses_amount'
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
                new Index('wako_tax_return_employments_tax_return_id_IDX', ['tax_return_id'], ''),
                new Index('wako_tax_return_employments_created_by_IDX', ['created_by'], ''),
                new Index('wako_tax_return_employments_created_at_IDX', ['created_at'], ''),
                new Index('wako_tax_return_employments_updated_at_IDX', ['updated_at'], ''),
                new Index('wako_tax_return_employments_updated_by_IDX', ['updated_by'], ''),
                new Index('wako_tax_return_employments_deleted_at_IDX', ['deleted_at'], ''),
                new Index('wako_tax_return_employments_deleted_by_IDX', ['deleted_by'], ''),
                new Index('wako_tax_return_employments_utr_IDX', ['utr'], ''),
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
