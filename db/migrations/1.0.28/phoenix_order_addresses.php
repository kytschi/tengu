<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class PhoenixOrderAddressesMigration_128
 */
class PhoenixOrderAddressesMigration_128 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('phoenix_order_addresses', [
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
                    'type',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "billing",
                        'notNull' => true,
                        'size' => 10,
                        'after' => 'order_id'
                    ]
                ),
                new Column(
                    'first_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'type'
                    ]
                ),
                new Column(
                    'last_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'first_name'
                    ]
                ),
                new Column(
                    'email',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'last_name'
                    ]
                ),
                new Column(
                    'phone',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'email'
                    ]
                ),
                new Column(
                    'company',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'phone'
                    ]
                ),
                new Column(
                    'address_line_1',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'company'
                    ]
                ),
                new Column(
                    'address_line_2',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'address_line_1'
                    ]
                ),
                new Column(
                    'town',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'address_line_2'
                    ]
                ),
                new Column(
                    'county',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'town'
                    ]
                ),
                new Column(
                    'country',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "United Kingdom",
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'county'
                    ]
                ),
                new Column(
                    'postcode',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'country'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'postcode'
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
                new Index('phoenix_order_addresses_address_line_1_IDX', ['address_line_1'], ''),
                new Index('phoenix_order_addresses_address_line_2_IDX', ['address_line_2'], ''),
                new Index('phoenix_order_addresses_company_IDX', ['company'], ''),
                new Index('phoenix_order_addresses_country_IDX', ['country'], ''),
                new Index('phoenix_order_addresses_county_IDX', ['county'], ''),
                new Index('phoenix_order_addresses_created_at_IDX', ['created_at'], ''),
                new Index('phoenix_order_addresses_created_by_IDX', ['created_by'], ''),
                new Index('phoenix_order_addresses_deleted_at_IDX', ['deleted_at'], ''),
                new Index('phoenix_order_addresses_deleted_by_IDX', ['deleted_by'], ''),
                new Index('phoenix_order_addresses_email_IDX', ['email'], ''),
                new Index('phoenix_order_addresses_first_name_IDX', ['first_name'], ''),
                new Index('phoenix_order_addresses_last_name_IDX', ['last_name'], ''),
                new Index('phoenix_order_addresses_order_id_IDX', ['order_id'], ''),
                new Index('phoenix_order_addresses_phone_IDX', ['phone'], ''),
                new Index('phoenix_order_addresses_postcode_IDX', ['postcode'], ''),
                new Index('phoenix_order_addresses_town_IDX', ['town'], ''),
                new Index('phoenix_order_addresses_type_IDX', ['type'], ''),
                new Index('phoenix_order_addresses_updated_at_IDX', ['updated_at'], ''),
                new Index('phoenix_order_addresses_updated_by_IDX', ['updated_by'], ''),
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
