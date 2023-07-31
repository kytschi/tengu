<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class PhoenixOrdersMigration_118
 */
class PhoenixOrdersMigration_118 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('phoenix_orders', [
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
                    'customer_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'number',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'notNull' => true,
                        'size' => 11,
                        'after' => 'customer_id'
                    ]
                ),
                new Column(
                    'sub_total',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => true,
                        'after' => 'number'
                    ]
                ),
                new Column(
                    'vat',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => true,
                        'after' => 'sub_total'
                    ]
                ),
                new Column(
                    'total',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => true,
                        'after' => 'vat'
                    ]
                ),
                new Column(
                    'quantity',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "1",
                        'notNull' => true,
                        'size' => 11,
                        'after' => 'total'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "in progress",
                        'notNull' => true,
                        'size' => 50,
                        'after' => 'quantity'
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
                new Column(
                    'dispatched_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => false,
                        'after' => 'deleted_by'
                    ]
                ),
                new Column(
                    'dispatched_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'dispatched_at'
                    ]
                ),
            ],
            'indexes' => [
                new Index('PRIMARY', ['id'], 'PRIMARY'),
                new Index('basket_created_at_IDX', ['created_at'], ''),
                new Index('basket_created_by_IDX', ['created_by'], ''),
                new Index('basket_deleted_at_IDX', ['deleted_at'], ''),
                new Index('basket_deleted_by_IDX', ['deleted_by'], ''),
                new Index('basket_total_IDX', ['total'], ''),
                new Index('basket_updated_at_IDX', ['updated_at'], ''),
                new Index('basket_updated_by_IDX', ['updated_by'], ''),
                new Index('phoenix_baskets_quanity_IDX', ['quantity'], ''),
                new Index('phoenix_baskets_status_IDX', ['status'], ''),
                new Index('phoenix_baskets_sub_total_IDX', ['sub_total'], ''),
                new Index('phoenix_baskets_vat_IDX', ['vat'], ''),
                new Index('phoenix_orders_dispatched_at_IDX', ['dispatched_at'], ''),
                new Index('phoenix_orders_dispatched_by_IDX', ['dispatched_by'], ''),
                new Index('phoenix_orders_number_IDX', ['number'], ''),
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
