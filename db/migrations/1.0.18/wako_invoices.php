<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class WakoInvoicesMigration_118
 */
class WakoInvoicesMigration_118 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('wako_invoices', [
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
                    'project_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'tax_year_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'project_id'
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
                        'notNull' => true,
                        'after' => 'name'
                    ]
                ),
                new Column(
                    'vat',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'amount'
                    ]
                ),
                new Column(
                    'sub_total',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'vat'
                    ]
                ),
                new Column(
                    'currency',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "GBP",
                        'notNull' => true,
                        'size' => 3,
                        'after' => 'sub_total'
                    ]
                ),
                new Column(
                    'timesheet_amount',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'currency'
                    ]
                ),
                new Column(
                    'search_tags',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'timesheet_amount'
                    ]
                ),
                new Column(
                    'direction',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "outgoing",
                        'notNull' => true,
                        'size' => 10,
                        'after' => 'search_tags'
                    ]
                ),
                new Column(
                    'issued_on',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => true,
                        'after' => 'direction'
                    ]
                ),
                new Column(
                    'paid_on',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => false,
                        'after' => 'issued_on'
                    ]
                ),
                new Column(
                    'taxable',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "1",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'paid_on'
                    ]
                ),
                new Column(
                    'ref',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'taxable'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "outstanding",
                        'notNull' => true,
                        'size' => 20,
                        'after' => 'ref'
                    ]
                ),
                new Column(
                    'printed_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => false,
                        'after' => 'status'
                    ]
                ),
                new Column(
                    'printed_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'printed_at'
                    ]
                ),
                new Column(
                    'emailed_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => false,
                        'after' => 'printed_by'
                    ]
                ),
                new Column(
                    'emailed_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'emailed_at'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'emailed_by'
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
                new Index('wako_invoices_amount_IDX', ['amount'], ''),
                new Index('wako_invoices_created_at_IDX', ['created_at'], ''),
                new Index('wako_invoices_created_by_IDX', ['created_by'], ''),
                new Index('wako_invoices_deleted_at_IDX', ['deleted_at'], ''),
                new Index('wako_invoices_deleted_by_IDX', ['deleted_by'], ''),
                new Index('wako_invoices_name_IDX', ['name'], ''),
                new Index('wako_invoices_status_IDX', ['status'], ''),
                new Index('wako_invoices_updated_at_IDX', ['updated_at'], ''),
                new Index('wako_invoices_updated_by_IDX', ['updated_by'], ''),
                new Index('wako_invoices_issued_on_IDX', ['issued_on'], ''),
                new Index('wako_invoices_paid_on_IDX', ['paid_on'], ''),
                new Index('wako_invoices_taxable_IDX', ['taxable'], ''),
                new Index('wako_invoices_tax_year_id_IDX', ['tax_year_id'], ''),
                new Index('wako_invoices_project_id_IDX', ['project_id'], ''),
                new Index('wako_invoices_printed_at_IDX', ['printed_at'], ''),
                new Index('wako_invoices_printed_by_IDX', ['printed_by'], ''),
                new Index('wako_invoices_emailed_at_IDX', ['emailed_at'], ''),
                new Index('wako_invoices_emailed_by_IDX', ['emailed_by'], ''),
                new Index('wako_invoices_ref_IDX', ['ref'], ''),
                new Index('wako_invoices_vat_IDX', ['vat'], ''),
                new Index('wako_invoices_sub_total_IDX', ['sub_total'], ''),
                new Index('wako_invoices_timesheet_amount_IDX', ['timesheet_amount'], ''),
                new Index('wako_invoices_direction_IDX', ['direction'], ''),
                new Index('wako_invoices_currency_IDX', ['currency'], ''),
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
