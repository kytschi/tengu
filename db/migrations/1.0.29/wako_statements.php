<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class WakoStatementsMigration_129
 */
class WakoStatementsMigration_129 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('wako_statements', [
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
                        'size' => 36,
                        'after' => 'tax_year_id'
                    ]
                ),
                new Column(
                    'in',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'name'
                    ]
                ),
                new Column(
                    'out',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'in'
                    ]
                ),
                new Column(
                    'currency',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "GBP",
                        'notNull' => true,
                        'size' => 3,
                        'after' => 'out'
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
                    'period_from',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => false,
                        'after' => 'search_tags'
                    ]
                ),
                new Column(
                    'period_to',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => false,
                        'after' => 'period_from'
                    ]
                ),
                new Column(
                    'type',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 50,
                        'after' => 'period_to'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "active",
                        'notNull' => true,
                        'size' => 10,
                        'after' => 'type'
                    ]
                ),
                new Column(
                    'taxable',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => true,
                        'size' => 1,
                        'after' => 'status'
                    ]
                ),
                new Column(
                    'created_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'taxable'
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
                new Index('wako_statements_name_IDX', ['name'], ''),
                new Index('wako_statements_in_IDX', ['in'], ''),
                new Index('wako_statements_out_IDX', ['out'], ''),
                new Index('wako_statements_created_by_IDX', ['created_by'], ''),
                new Index('wako_statements_created_at_IDX', ['created_at'], ''),
                new Index('wako_statements_updated_by_IDX', ['updated_by'], ''),
                new Index('wako_statements_updated_at_IDX', ['updated_at'], ''),
                new Index('wako_statements_deleted_by_IDX', ['deleted_by'], ''),
                new Index('wako_statements_deleted_at_IDX', ['deleted_at'], ''),
                new Index('wako_statements_status_IDX', ['status'], ''),
                new Index('wako_statements_period_from_IDX', ['period_from'], ''),
                new Index('wako_statements_period_to_IDX', ['period_to'], ''),
                new Index('wako_statements_type_IDX', ['type'], ''),
                new Index('wako_statements_tax_year_id_IDX', ['tax_year_id'], ''),
                new Index('wako_statements_taxable_IDX', ['taxable'], ''),
                new Index('wako_statements_currency_IDX', ['currency'], ''),
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
