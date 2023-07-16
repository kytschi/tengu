<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class FilesMigration_103
 */
class FilesMigration_103 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('files', [
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
                        'size' => 50,
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
                    'mime_type',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'resource_id'
                    ]
                ),
                new Column(
                    'name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'mime_type'
                    ]
                ),
                new Column(
                    'label',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'name'
                    ]
                ),
                new Column(
                    'filename',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'label'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "active",
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'filename'
                    ]
                ),
                new Column(
                    'compress',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'status'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'compress'
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
                new Index('files_type_IDX', ['resource'], ''),
                new Index('files_mime_type_IDX', ['mime_type'], ''),
                new Index('files_name_IDX', ['name'], ''),
                new Index('files_filename_IDX', ['filename'], ''),
                new Index('files_status_IDX', ['status'], ''),
                new Index('files_created_at_IDX', ['created_at'], ''),
                new Index('files_created_by_IDX', ['created_by'], ''),
                new Index('files_updated_at_IDX', ['updated_at'], ''),
                new Index('files_updated_by_IDX', ['updated_by'], ''),
                new Index('files_deleted_at_IDX', ['deleted_at'], ''),
                new Index('files_deleted_by_IDX', ['deleted_by'], ''),
                new Index('files_resource_id_IDX', ['resource_id'], ''),
                new Index('files_compress_IDX', ['compress'], ''),
            ],
            'options' => [
                'TABLE_TYPE' => 'BASE TABLE',
                'AUTO_INCREMENT' => '',
                'ENGINE' => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci',
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
