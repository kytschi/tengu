<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class WakoSettingsMigration_100
 */
class WakoSettingsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('wako_settings', [
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
                    'currency',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "GBP",
                        'notNull' => true,
                        'size' => 3,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'currency'
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
                new Index('wako_settings_created_at_IDX', ['created_at'], ''),
                new Index('wako_settings_created_by_IDX', ['created_by'], ''),
                new Index('wako_settings_updated_at_IDX', ['updated_at'], ''),
                new Index('wako_settings_updated_by_IDX', ['updated_by'], ''),
                new Index('wako_settings_deleted_at_IDX', ['deleted_at'], ''),
                new Index('wako_settings_deleted_by_IDX', ['deleted_by'], ''),
                new Index('wako_settings_currency_IDX', ['currency'], ''),
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
        $statement = self::$connection->prepare(
            'INSERT INTO wako_settings
                (id, currency, created_at, created_by, updated_at, updated_by)
                SELECT
                    :id,
                    :currency,
                    :created_at,
                    :created_by,
                    :updated_at,
                    :updated_by 
                FROM DUAL WHERE NOT EXISTS
                (
                    SELECT 
                        id, 
                        currency, 
                        created_at, 
                        created_by, 
                        updated_at, 
                        updated_by
                    FROM wako_settings
                    WHERE id=:id 
                );'
        );
        self::$connection->executePrepared(
            $statement,
            [
                'id' => '00000000-0000-0000-0000-000000000000',
                'currency' => 'GBP',
                'created_by' => '00000000-0000-0000-0000-000000000000',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_by' => '00000000-0000-0000-0000-000000000000',
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => Column::TYPE_VARCHAR,
                'currency' => Column::TYPE_VARCHAR,
                'created_by' => Column::TYPE_VARCHAR,
                'created_at' => Column::TYPE_DATETIME,
                'updated_by' => Column::TYPE_VARCHAR,
                'updated_at' => Column::TYPE_DATETIME,
            ]
        );
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
