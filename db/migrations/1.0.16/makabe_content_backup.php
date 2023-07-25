<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class MakabeContentBackupMigration_116
 */
class MakabeContentBackupMigration_116 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('makabe_content_backup', [
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
                    'resource_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'resource',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 50,
                        'after' => 'resource_id'
                    ]
                ),
                new Column(
                    'title',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'resource'
                    ]
                ),
                new Column(
                    'url',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'title'
                    ]
                ),
                new Column(
                    'canonical_url',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'url'
                    ]
                ),
                new Column(
                    'meta_description',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'canonical_url'
                    ]
                ),
                new Column(
                    'created_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'meta_description'
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
                new Index('makabe_content_backup_canonical_url_IDX', ['canonical_url'], ''),
                new Index('makabe_content_backup_created_at_IDX', ['created_at'], ''),
                new Index('makabe_content_backup_created_by_IDX', ['created_by'], ''),
                new Index('makabe_content_backup_deleted_at_IDX', ['deleted_at'], ''),
                new Index('makabe_content_backup_deleted_by_IDX', ['deleted_by'], ''),
                new Index('makabe_content_backup_id_IDX', ['id'], ''),
                new Index('makabe_content_backup_meta_description_IDX', ['meta_description'], ''),
                new Index('makabe_content_backup_resource_IDX', ['resource'], ''),
                new Index('makabe_content_backup_resource_id_IDX', ['resource_id'], ''),
                new Index('makabe_content_backup_title_IDX', ['title'], ''),
                new Index('makabe_content_backup_updated_at_IDX', ['updated_at'], ''),
                new Index('makabe_content_backup_updated_by_IDX', ['updated_by'], ''),
                new Index('makabe_content_backup_url_IDX', ['url'], ''),
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
