<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class PhoenixOrderItemsMigration_109
 */
class PhoenixOrderItemsMigration_109 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('phoenix_order_items', [
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
                    'order_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'product_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'order_id'
                    ]
                ),
                new Column(
                    'quantity',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "1",
                        'notNull' => true,
                        'size' => 11,
                        'after' => 'product_id'
                    ]
                ),
                new Column(
                    'fulfilled',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 11,
                        'after' => 'quantity'
                    ]
                ),
                new Column(
                    'sub_total',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => true,
                        'after' => 'fulfilled'
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
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'total'
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
                new Index('phoenix_basket_items_price_IDX', ['sub_total'], ''),
                new Index('phoenix_basket_items_basket_id_IDX', ['order_id'], ''),
                new Index('phoenix_basket_items_created_at_IDX', ['created_at'], ''),
                new Index('phoenix_basket_items_created_by_IDX', ['created_by'], ''),
                new Index('phoenix_basket_items_updated_at_IDX', ['updated_at'], ''),
                new Index('phoenix_basket_items_updated_by_IDX', ['updated_by'], ''),
                new Index('phoenix_basket_items_deleted_at_IDX', ['deleted_at'], ''),
                new Index('phoenix_basket_items_deleted_by_IDX', ['deleted_by'], ''),
                new Index('phoenix_basket_items_product_id_IDX', ['product_id'], ''),
                new Index('phoenix_basket_items_quanity_IDX', ['quantity'], ''),
                new Index('phoenix_basket_items_vat_IDX', ['vat'], ''),
                new Index('phoenix_basket_items_total_IDX', ['total'], ''),
                new Index('phoenix_order_items_fulfilled_IDX', ['fulfilled'], ''),
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
