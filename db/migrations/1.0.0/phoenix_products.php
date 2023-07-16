<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class PhoenixProductsMigration_100
 */
class PhoenixProductsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('phoenix_products', [
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
                    'page_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'code',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'page_id'
                    ]
                ),
                new Column(
                    'price',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'notNull' => true,
                        'scale' => 2,
                        'after' => 'code'
                    ]
                ),
                new Column(
                    'stock',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "1",
                        'notNull' => true,
                        'size' => 11,
                        'after' => 'price'
                    ]
                ),
                new Column(
                    'low_stock',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "5",
                        'notNull' => true,
                        'size' => 11,
                        'after' => 'stock'
                    ]
                ),
                new Column(
                    'vat',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "25",
                        'notNull' => true,
                        'after' => 'low_stock'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'vat'
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
                new Index('phoenix_products_code_IDX', ['code'], ''),
                new Index('phoenix_products_created_at_IDX', ['created_at'], ''),
                new Index('phoenix_products_created_by_IDX', ['created_by'], ''),
                new Index('phoenix_products_deleted_at_IDX', ['deleted_at'], ''),
                new Index('phoenix_products_deleted_by_IDX', ['deleted_by'], ''),
                new Index('phoenix_products_low_stock_IDX', ['low_stock'], ''),
                new Index('phoenix_products_page_id_IDX', ['page_id'], ''),
                new Index('phoenix_products_price_IDX', ['price'], ''),
                new Index('phoenix_products_stock_IDX', ['stock'], ''),
                new Index('phoenix_products_updated_at_IDX', ['updated_at'], ''),
                new Index('phoenix_products_updated_by_IDX', ['updated_by'], ''),
                new Index('phoenix_products_vat_IDX', ['vat'], ''),
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
