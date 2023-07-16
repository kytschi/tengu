<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class ClientsMigration_110
 */
class ClientsMigration_110 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('clients', [
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
                    'name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'billing_address_line_1',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'name'
                    ]
                ),
                new Column(
                    'billing_address_line_2',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'billing_address_line_1'
                    ]
                ),
                new Column(
                    'billing_city',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'billing_address_line_2'
                    ]
                ),
                new Column(
                    'billing_county',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'billing_city'
                    ]
                ),
                new Column(
                    'billing_country',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'billing_county'
                    ]
                ),
                new Column(
                    'billing_postcode',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 50,
                        'after' => 'billing_country'
                    ]
                ),
                new Column(
                    'billing_fullname',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'billing_postcode'
                    ]
                ),
                new Column(
                    'billing_phone_number',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'billing_fullname'
                    ]
                ),
                new Column(
                    'billing_email',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'billing_phone_number'
                    ]
                ),
                new Column(
                    'shipping_address_line_1',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'billing_email'
                    ]
                ),
                new Column(
                    'shipping_address_line_2',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'shipping_address_line_1'
                    ]
                ),
                new Column(
                    'shipping_city',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'shipping_address_line_2'
                    ]
                ),
                new Column(
                    'shipping_county',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'shipping_city'
                    ]
                ),
                new Column(
                    'shipping_country',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'shipping_county'
                    ]
                ),
                new Column(
                    'shipping_postcode',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'shipping_country'
                    ]
                ),
                new Column(
                    'shipping_fullname',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'shipping_postcode'
                    ]
                ),
                new Column(
                    'shipping_phone_number',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'shipping_fullname'
                    ]
                ),
                new Column(
                    'shipping_email',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'shipping_phone_number'
                    ]
                ),
                new Column(
                    'search_tags',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'shipping_email'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "active",
                        'notNull' => true,
                        'size' => 20,
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
                new Index('clients_billing_address_line_1_IDX', ['billing_address_line_1'], ''),
                new Index('clients_billing_address_line_2_IDX', ['billing_address_line_2'], ''),
                new Index('clients_billing_city_IDX', ['billing_city'], ''),
                new Index('clients_billing_country_IDX', ['billing_country'], ''),
                new Index('clients_billing_county_IDX', ['billing_county'], ''),
                new Index('clients_billing_email_IDX', ['billing_email'], ''),
                new Index('clients_billing_fullname_IDX', ['billing_fullname'], ''),
                new Index('clients_billing_phone_number_IDX', ['billing_phone_number'], ''),
                new Index('clients_billing_postcode_IDX', ['billing_postcode'], ''),
                new Index('clients_created_at_IDX', ['created_at'], ''),
                new Index('clients_created_by_IDX', ['created_by'], ''),
                new Index('clients_deleted_at_IDX', ['deleted_at'], ''),
                new Index('clients_deleted_by_IDX', ['deleted_by'], ''),
                new Index('clients_id_IDX', ['id'], ''),
                new Index('clients_name_IDX', ['name'], ''),
                new Index('clients_shipping_address_line_1_IDX', ['shipping_address_line_1'], ''),
                new Index('clients_shipping_address_line_2_IDX', ['shipping_address_line_2'], ''),
                new Index('clients_shipping_city_IDX', ['shipping_city'], ''),
                new Index('clients_shipping_country_IDX', ['shipping_country'], ''),
                new Index('clients_shipping_county_IDX', ['shipping_county'], ''),
                new Index('clients_shipping_email_IDX', ['shipping_email'], ''),
                new Index('clients_shipping_fullname_IDX', ['shipping_fullname'], ''),
                new Index('clients_shipping_phone_number_IDX', ['shipping_phone_number'], ''),
                new Index('clients_shipping_postcode_IDX', ['shipping_postcode'], ''),
                new Index('clients_status_IDX', ['status'], ''),
                new Index('clients_updated_at_IDX', ['updated_at'], ''),
                new Index('clients_updated_by_IDX', ['updated_by'], ''),
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
