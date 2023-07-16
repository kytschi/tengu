<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class MakabeScanPagesMigration_110
 */
class MakabeScanPagesMigration_110 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('makabe_scan_pages', [
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
                    'campaign_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'campaign_id'
                    ]
                ),
                new Column(
                    'url',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'name'
                    ]
                ),
                new Column(
                    'rank',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "0",
                        'notNull' => true,
                        'size' => 11,
                        'after' => 'url'
                    ]
                ),
                new Column(
                    'last_scanned',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'rank'
                    ]
                ),
                new Column(
                    'scan_status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "not scanned",
                        'notNull' => true,
                        'size' => 20,
                        'after' => 'last_scanned'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "active",
                        'notNull' => true,
                        'size' => 20,
                        'after' => 'scan_status'
                    ]
                ),
                new Column(
                    'created_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'status'
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
                new Index('makabe_scan_pages_campaign_id_IDX', ['campaign_id'], ''),
                new Index('makabe_scan_sites_created_at_IDX', ['created_at'], ''),
                new Index('makabe_scan_sites_created_by_IDX', ['created_by'], ''),
                new Index('makabe_scan_sites_deleted_at_IDX', ['deleted_at'], ''),
                new Index('makabe_scan_sites_deleted_by_IDX', ['deleted_by'], ''),
                new Index('makabe_scan_sites_id_IDX', ['id'], ''),
                new Index('makabe_scan_sites_name_IDX', ['name'], ''),
                new Index('makabe_scan_sites_updated_at_IDX', ['updated_at'], ''),
                new Index('makabe_scan_sites_updated_by_IDX', ['updated_by'], ''),
                new Index('makabe_scan_sites_url_IDX', ['url'], ''),
                new Index('makabe_scan_pages_rank_IDX', ['rank'], ''),
                new Index('makabe_scan_pages_status_IDX', ['status'], ''),
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
