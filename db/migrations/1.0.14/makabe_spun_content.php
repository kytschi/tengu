<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class MakabeSpunContentMigration_114
 */
class MakabeSpunContentMigration_114 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('makabe_spun_content', [
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
                    'spin_content_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'resource_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'spin_content_id'
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
                    'url',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'resource'
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
                    'name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'canonical_url'
                    ]
                ),
                new Column(
                    'content',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => true,
                        'after' => 'name'
                    ]
                ),
                new Column(
                    'meta_keywords',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'content'
                    ]
                ),
                new Column(
                    'meta_description',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'meta_keywords'
                    ]
                ),
                new Column(
                    'sort',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "1",
                        'notNull' => true,
                        'size' => 11,
                        'after' => 'meta_description'
                    ]
                ),
                new Column(
                    'used_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => false,
                        'after' => 'sort'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "pending",
                        'notNull' => true,
                        'size' => 20,
                        'after' => 'used_at'
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
                new Index('makabe_spun_content_canonical_url_IDX', ['canonical_url'], ''),
                new Index('makabe_spun_content_created_at_IDX', ['created_at'], ''),
                new Index('makabe_spun_content_created_by_IDX', ['created_by'], ''),
                new Index('makabe_spun_content_deleted_at_IDX', ['deleted_at'], ''),
                new Index('makabe_spun_content_deleted_by_IDX', ['deleted_by'], ''),
                new Index('makabe_spun_content_id_IDX', ['id'], ''),
                new Index('makabe_spun_content_meta_description_IDX', ['meta_description'], ''),
                new Index('makabe_spun_content_meta_keywords_IDX', ['meta_keywords'], ''),
                new Index('makabe_spun_content_resource_IDX', ['resource'], ''),
                new Index('makabe_spun_content_resource_id_IDX', ['resource_id'], ''),
                new Index('makabe_spun_content_spin_content_id_IDX', ['spin_content_id'], ''),
                new Index('makabe_spun_content_title_IDX', ['name'], ''),
                new Index('makabe_spun_content_updated_at_IDX', ['updated_at'], ''),
                new Index('makabe_spun_content_updated_by_IDX', ['updated_by'], ''),
                new Index('makabe_spun_content_url_IDX', ['url'], ''),
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
