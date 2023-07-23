<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class PhoenixOrderShippingMigration_115
 */
class PhoenixOrderShippingMigration_115 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('phoenix_order_shipping', [
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
                    'shipping_company_code',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'order_id'
                    ]
                ),
                new Column(
                    'shipping_option',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'shipping_company_code'
                    ]
                ),
                new Column(
                    'parcel_count',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "1",
                        'notNull' => true,
                        'size' => 11,
                        'after' => 'shipping_option'
                    ]
                ),
                new Column(
                    'weight',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'notNull' => true,
                        'after' => 'parcel_count'
                    ]
                ),
                new Column(
                    'width',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'notNull' => true,
                        'after' => 'weight'
                    ]
                ),
                new Column(
                    'height',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'notNull' => true,
                        'after' => 'width'
                    ]
                ),
                new Column(
                    'length',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'notNull' => true,
                        'after' => 'height'
                    ]
                ),
                new Column(
                    'amount',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'notNull' => true,
                        'after' => 'length'
                    ]
                ),
                new Column(
                    'shipping_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'amount'
                    ]
                ),
                new Column(
                    'label',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'shipping_id'
                    ]
                ),
                new Column(
                    'tracking_code',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'label'
                    ]
                ),
                new Column(
                    'shipping_charge',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'tracking_code'
                    ]
                ),
                new Column(
                    'created_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'shipping_charge'
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
                new Index('phoenix_order_shipping_amount_IDX', ['amount'], ''),
                new Index('phoenix_order_shipping_created_at_IDX', ['created_at'], ''),
                new Index('phoenix_order_shipping_created_by_IDX', ['created_by'], ''),
                new Index('phoenix_order_shipping_deleted_at_IDX', ['deleted_at'], ''),
                new Index('phoenix_order_shipping_deleted_by_IDX', ['deleted_by'], ''),
                new Index('phoenix_order_shipping_height_IDX', ['height'], ''),
                new Index('phoenix_order_shipping_length_IDX', ['length'], ''),
                new Index('phoenix_order_shipping_order_id_IDX', ['order_id'], ''),
                new Index('phoenix_order_shipping_parcel_count_IDX', ['parcel_count'], ''),
                new Index('phoenix_order_shipping_shipping_charge_IDX', ['shipping_charge'], ''),
                new Index('phoenix_order_shipping_shipping_company_code_IDX', ['shipping_company_code'], ''),
                new Index('phoenix_order_shipping_shipping_id_IDX', ['shipping_id'], ''),
                new Index('phoenix_order_shipping_shipping_option_IDX', ['shipping_option'], ''),
                new Index('phoenix_order_shipping_tracking_code_IDX', ['tracking_code'], ''),
                new Index('phoenix_order_shipping_updated_at_IDX', ['updated_at'], ''),
                new Index('phoenix_order_shipping_updated_by_IDX', ['updated_by'], ''),
                new Index('phoenix_order_shipping_weight_IDX', ['weight'], ''),
                new Index('phoenix_order_shipping_width_IDX', ['width'], ''),
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
