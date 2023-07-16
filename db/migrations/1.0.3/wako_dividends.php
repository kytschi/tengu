<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class WakoDividendsMigration_103
 */
class WakoDividendsMigration_103 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('wako_dividends', [
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
                    'shareholder_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
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
                        'after' => 'shareholder_id'
                    ]
                ),
                new Column(
                    'statement_item_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'tax_year_id'
                    ]
                ),
                new Column(
                    'number',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "1",
                        'notNull' => true,
                        'size' => 11,
                        'after' => 'statement_item_id'
                    ]
                ),
                new Column(
                    'amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => true,
                        'after' => 'number'
                    ]
                ),
                new Column(
                    'issued_on',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => false,
                        'after' => 'amount'
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
                    'search_tags',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'paid_on'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "pending",
                        'notNull' => true,
                        'size' => 10,
                        'after' => 'search_tags'
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
                new Index('wako_dividends_tax_year_id_IDX', ['tax_year_id'], ''),
                new Index('wako_dividends_user_id_IDX', ['shareholder_id'], ''),
                new Index('wako_dividends_amount_IDX', ['amount'], ''),
                new Index('wako_dividends_issued_on_IDX', ['issued_on'], ''),
                new Index('wako_dividends_status_IDX', ['status'], ''),
                new Index('wako_dividends_created_at_IDX', ['created_at'], ''),
                new Index('wako_dividends_created_by_IDX', ['created_by'], ''),
                new Index('wako_dividends_updated_at_IDX', ['updated_at'], ''),
                new Index('wako_dividends_updated_by_IDX', ['updated_by'], ''),
                new Index('wako_dividends_deleted_at_IDX', ['deleted_at'], ''),
                new Index('wako_dividends_deleted_by_IDX', ['deleted_by'], ''),
                new Index('wako_dividends_paid_on_IDX', ['paid_on'], ''),
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
