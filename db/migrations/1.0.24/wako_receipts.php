<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class WakoReceiptsMigration_124
 */
class WakoReceiptsMigration_124 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('wako_receipts', [
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
                    'tax_year_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'tax_year_id'
                    ]
                ),
                new Column(
                    'amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'name'
                    ]
                ),
                new Column(
                    'currency',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "GBP",
                        'notNull' => true,
                        'size' => 3,
                        'after' => 'amount'
                    ]
                ),
                new Column(
                    'search_tags',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'currency'
                    ]
                ),
                new Column(
                    'issued_on',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => false,
                        'after' => 'search_tags'
                    ]
                ),
                new Column(
                    'taxable',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "1",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'issued_on'
                    ]
                ),
                new Column(
                    'type',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 50,
                        'after' => 'taxable'
                    ]
                ),
                new Column(
                    'ref',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'type'
                    ]
                ),
                new Column(
                    'original_amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'ref'
                    ]
                ),
                new Column(
                    'original_currency',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "GBP",
                        'notNull' => false,
                        'size' => 3,
                        'after' => 'original_amount'
                    ]
                ),
                new Column(
                    'vat',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'original_currency'
                    ]
                ),
                new Column(
                    'sub_total',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'notNull' => false,
                        'after' => 'vat'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "active",
                        'notNull' => true,
                        'size' => 20,
                        'after' => 'sub_total'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'status'
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
                new Index('wako_receipts_amount_IDX', ['amount'], ''),
                new Index('wako_receipts_created_at_IDX', ['created_at'], ''),
                new Index('wako_receipts_created_by_IDX', ['created_by'], ''),
                new Index('wako_receipts_deleted_at_IDX', ['deleted_at'], ''),
                new Index('wako_receipts_deleted_by_IDX', ['deleted_by'], ''),
                new Index('wako_receipts_id_IDX', ['id'], ''),
                new Index('wako_receipts_name_IDX', ['name'], ''),
                new Index('wako_receipts_status_IDX', ['status'], ''),
                new Index('wako_receipts_updated_at_IDX', ['updated_at'], ''),
                new Index('wako_receipts_updated_by_IDX', ['updated_by'], ''),
                new Index('wako_receipts_chargeable_IDX', ['taxable'], ''),
                new Index('wako_receipts_issued_on_IDX', ['issued_on'], ''),
                new Index('wako_receipts_tax_year_id_IDX', ['tax_year_id'], ''),
                new Index('wako_receipts_type_IDX', ['type'], ''),
                new Index('wako_receipts_ref_IDX', ['ref'], ''),
                new Index('wako_receipts_original_amount_IDX', ['original_amount'], ''),
                new Index('wako_receipts_currency_IDX', ['currency'], ''),
                new Index('wako_receipts_vat_IDX', ['vat'], ''),
                new Index('wako_receipts_sub_total_IDX', ['sub_total'], ''),
                new Index('wako_receipts_original_currency_IDX', ['original_currency'], ''),
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
