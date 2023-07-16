<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class WakoStatementItemsMigration_106
 */
class WakoStatementItemsMigration_106 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('wako_statement_items', [
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
                    'statement_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'invoice_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'statement_id'
                    ]
                ),
                new Column(
                    'shareholder_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'invoice_id'
                    ]
                ),
                new Column(
                    'tax_year_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'shareholder_id'
                    ]
                ),
                new Column(
                    'employee_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'tax_year_id'
                    ]
                ),
                new Column(
                    'expenses_employee_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'employee_id'
                    ]
                ),
                new Column(
                    'project_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'expenses_employee_id'
                    ]
                ),
                new Column(
                    'description',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'project_id'
                    ]
                ),
                new Column(
                    'out',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'description'
                    ]
                ),
                new Column(
                    'in',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'notNull' => false,
                        'after' => 'out'
                    ]
                ),
                new Column(
                    'currency',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "GBP",
                        'notNull' => true,
                        'size' => 3,
                        'after' => 'in'
                    ]
                ),
                new Column(
                    'balance',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'currency'
                    ]
                ),
                new Column(
                    'taxable',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'balance'
                    ]
                ),
                new Column(
                    'search_tags',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'taxable'
                    ]
                ),
                new Column(
                    'processed_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => false,
                        'after' => 'search_tags'
                    ]
                ),
                new Column(
                    'created_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'processed_at'
                    ]
                ),
                new Column(
                    'created_at',
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
                        'after' => 'created_at'
                    ]
                ),
                new Column(
                    'updated_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'updated_by'
                    ]
                ),
                new Column(
                    'deleted_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'updated_at'
                    ]
                ),
                new Column(
                    'deleted_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => false,
                        'after' => 'deleted_by'
                    ]
                ),
            ],
            'indexes' => [
                new Index('PRIMARY', ['id'], 'PRIMARY'),
                new Index('wako_statement_items_statement_id_IDX', ['statement_id'], ''),
                new Index('wako_statement_items_out_IDX', ['out'], ''),
                new Index('wako_statement_items_in_IDX', ['in'], ''),
                new Index('wako_statement_items_balance_IDX', ['balance'], ''),
                new Index('wako_statement_items_created_by_IDX', ['created_by'], ''),
                new Index('wako_statement_items_created_at_IDX', ['created_at'], ''),
                new Index('wako_statement_items_updated_by_IDX', ['updated_by'], ''),
                new Index('wako_statement_items_updated_at_IDX', ['updated_at'], ''),
                new Index('wako_statement_items_deleted_by_IDX', ['deleted_by'], ''),
                new Index('wako_statement_items_deleted_at_IDX', ['deleted_at'], ''),
                new Index('wako_statement_items_chareable_IDX', ['taxable'], ''),
                new Index('wako_statement_items_processed_at_IDX', ['processed_at'], ''),
                new Index('wako_statement_items_invoice_id_IDX', ['invoice_id'], ''),
                new Index('wako_statement_items_tax_year_id_IDX', ['tax_year_id'], ''),
                new Index('wako_statement_items_employee_id_IDX', ['employee_id'], ''),
                new Index('wako_statement_items_project_id_IDX', ['project_id'], ''),
                new Index('wako_statement_items_shareholder_id_IDX', ['shareholder_id'], ''),
                new Index('wako_statement_items_currency_IDX', ['currency'], ''),
                new Index('wako_statement_items_expenses_employee_id_IDX', ['expenses_employee_id'], ''),
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
