<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class WakoSettingsMigration_117
 */
class WakoSettingsMigration_117 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('wako_settings', [
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
                    'registered_company_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'registered_company_address',
                    [
                        'type' => Column::TYPE_TINYTEXT,
                        'notNull' => false,
                        'after' => 'registered_company_name'
                    ]
                ),
                new Column(
                    'registered_company_number',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'registered_company_address'
                    ]
                ),
                new Column(
                    'currency',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "GBP",
                        'notNull' => true,
                        'size' => 3,
                        'after' => 'registered_company_number'
                    ]
                ),
                new Column(
                    'paye_tax_ref_number',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'currency'
                    ]
                ),
                new Column(
                    'shares',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "1",
                        'notNull' => false,
                        'size' => 11,
                        'after' => 'paye_tax_ref_number'
                    ]
                ),
                new Column(
                    'secretary_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'shares'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'secretary_id'
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
                new Index('wako_settings_created_at_IDX', ['created_at'], ''),
                new Index('wako_settings_created_by_IDX', ['created_by'], ''),
                new Index('wako_settings_updated_at_IDX', ['updated_at'], ''),
                new Index('wako_settings_updated_by_IDX', ['updated_by'], ''),
                new Index('wako_settings_deleted_at_IDX', ['deleted_at'], ''),
                new Index('wako_settings_deleted_by_IDX', ['deleted_by'], ''),
                new Index('wako_settings_currency_IDX', ['currency'], ''),
                new Index('wako_settings_registered_company_name_IDX', ['registered_company_name'], ''),
                new Index('wako_settings_paye_tax_ref_number_IDX', ['paye_tax_ref_number'], ''),
                new Index('wako_settings_shares_IDX', ['shares'], ''),
                new Index('wako_settings_registered_company_number_IDX', ['registered_company_number'], ''),
                new Index('wako_settings_secretary_id_IDX', ['secretary_id'], ''),
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
