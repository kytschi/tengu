<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class StatsMigration_129
 */
class StatsMigration_129 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('stats', [
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
                    'resource',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 100,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'resource_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'resource'
                    ]
                ),
                new Column(
                    'visitor',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'resource_id'
                    ]
                ),
                new Column(
                    'parent_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'visitor'
                    ]
                ),
                new Column(
                    'referer',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'parent_id'
                    ]
                ),
                new Column(
                    'bot',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'referer'
                    ]
                ),
                new Column(
                    'agent',
                    [
                        'type' => Column::TYPE_MEDIUMTEXT,
                        'notNull' => false,
                        'after' => 'bot'
                    ]
                ),
                new Column(
                    'browser',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'agent'
                    ]
                ),
                new Column(
                    'operating_system',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'browser'
                    ]
                ),
                new Column(
                    'country',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'operating_system'
                    ]
                ),
                new Column(
                    'latitude',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'country'
                    ]
                ),
                new Column(
                    'longitude',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'latitude'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'longitude'
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
            ],
            'indexes' => [
                new Index('PRIMARY', ['id'], 'PRIMARY'),
                new Index('stats_resource_id_IDX', ['resource_id'], ''),
                new Index('stats_resource_IDX', ['resource'], ''),
                new Index('stats_parent_id_IDX', ['parent_id'], ''),
                new Index('stats_visitor_IDX', ['visitor'], ''),
                new Index('stats_referer_IDX', ['referer'], ''),
                new Index('stats_bot_IDX', ['bot'], ''),
                new Index('stats_created_at_IDX', ['created_at'], ''),
                new Index('stats_created_by_IDX', ['created_by'], ''),
                new Index('stats_updated_at_IDX', ['updated_at'], ''),
                new Index('stats_updated_by_IDX', ['updated_by'], ''),
                new Index('stats_browser_IDX', ['browser'], ''),
                new Index('stats_operating_system_IDX', ['operating_system'], ''),
                new Index('stats_country_IDX', ['country'], ''),
                new Index('stats_latitude_IDX', ['latitude'], ''),
                new Index('stats_longitude_IDX', ['longitude'], ''),
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
