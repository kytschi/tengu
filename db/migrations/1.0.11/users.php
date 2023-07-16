<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class UsersMigration_111
 */
class UsersMigration_111 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('users', [
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
                    'group_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'email',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'group_id'
                    ]
                ),
                new Column(
                    'password',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'email'
                    ]
                ),
                new Column(
                    'first_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'password'
                    ]
                ),
                new Column(
                    'last_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'first_name'
                    ]
                ),
                new Column(
                    'phone',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'last_name'
                    ]
                ),
                new Column(
                    'last_login',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => false,
                        'after' => 'phone'
                    ]
                ),
                new Column(
                    'login_attempts',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 11,
                        'after' => 'last_login'
                    ]
                ),
                new Column(
                    'temp_password',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'login_attempts'
                    ]
                ),
                new Column(
                    'shareholder',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "100",
                        'notNull' => false,
                        'after' => 'temp_password'
                    ]
                ),
                new Column(
                    'dob',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'shareholder'
                    ]
                ),
                new Column(
                    'utr',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'dob'
                    ]
                ),
                new Column(
                    'employee_ref',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'utr'
                    ]
                ),
                new Column(
                    'national_insurance',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'employee_ref'
                    ]
                ),
                new Column(
                    'address_line_1',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'national_insurance'
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
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'address_line_2'
                    ]
                ),
                new Column(
                    'county',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'town'
                    ]
                ),
                new Column(
                    'country',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'county'
                    ]
                ),
                new Column(
                    'postcode',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'country'
                    ]
                ),
                new Column(
                    'type',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "customer",
                        'notNull' => true,
                        'size' => 20,
                        'after' => 'postcode'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "inactive",
                        'notNull' => true,
                        'size' => 50,
                        'after' => 'type'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => false,
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
                        'notNull' => false,
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
                new Index('users_email_IDX', ['email'], ''),
                new Index('users_first_name_IDX', ['first_name'], ''),
                new Index('users_group_id_IDX', ['group_id'], ''),
                new Index('users_id_IDX', ['id'], ''),
                new Index('users_last_name_IDX', ['last_name'], ''),
                new Index('users_status_IDX', ['status'], ''),
                new Index('users_type_IDX', ['type'], ''),
                new Index('users_phone_IDX', ['phone'], ''),
                new Index('users_created_at_IDX', ['created_at'], ''),
                new Index('users_created_by_IDX', ['created_by'], ''),
                new Index('users_updated_at_IDX', ['updated_at'], ''),
                new Index('users_updated_by_IDX', ['updated_by'], ''),
                new Index('users_deleted_at_IDX', ['deleted_at'], ''),
                new Index('users_deleted_by_IDX', ['deleted_by'], ''),
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
