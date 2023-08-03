<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class PagesMigration_121
 */
class PagesMigration_121 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('pages', [
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
                    'template_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'parent_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'template_id'
                    ]
                ),
                new Column(
                    'campaign_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'parent_id'
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
                    'summary',
                    [
                        'type' => Column::TYPE_MEDIUMTEXT,
                        'notNull' => false,
                        'after' => 'url'
                    ]
                ),
                new Column(
                    'content',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'summary'
                    ]
                ),
                new Column(
                    'slogan',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'content'
                    ]
                ),
                new Column(
                    'meta_keywords',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'slogan'
                    ]
                ),
                new Column(
                    'meta_description',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'meta_keywords'
                    ]
                ),
                new Column(
                    'meta_author',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'meta_description'
                    ]
                ),
                new Column(
                    'canonical_url',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'meta_author'
                    ]
                ),
                new Column(
                    'type',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "page",
                        'notNull' => true,
                        'size' => 50,
                        'after' => 'canonical_url'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "active",
                        'notNull' => true,
                        'size' => 20,
                        'after' => 'type'
                    ]
                ),
                new Column(
                    'searchable',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "1",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'status'
                    ]
                ),
                new Column(
                    'search_tags',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'searchable'
                    ]
                ),
                new Column(
                    'sitemap',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "1",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'search_tags'
                    ]
                ),
                new Column(
                    'cover_image_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'sitemap'
                    ]
                ),
                new Column(
                    'banner_image_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'cover_image_id'
                    ]
                ),
                new Column(
                    'spinnable',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'banner_image_id'
                    ]
                ),
                new Column(
                    'spin_label',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'spinnable'
                    ]
                ),
                new Column(
                    'spin_content',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'spin_label'
                    ]
                ),
                new Column(
                    'pre_spin_content',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'spin_content'
                    ]
                ),
                new Column(
                    'pre_spin_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'pre_spin_content'
                    ]
                ),
                new Column(
                    'last_spun',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => false,
                        'after' => 'pre_spin_name'
                    ]
                ),
                new Column(
                    'spins',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 11,
                        'after' => 'last_spun'
                    ]
                ),
                new Column(
                    'spin_content_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 36,
                        'after' => 'spins'
                    ]
                ),
                new Column(
                    'sort',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "0",
                        'notNull' => true,
                        'size' => 11,
                        'after' => 'spin_content_id'
                    ]
                ),
                new Column(
                    'feature',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'sort'
                    ]
                ),
                new Column(
                    'rating',
                    [
                        'type' => Column::TYPE_FLOAT,
                        'default' => "0",
                        'notNull' => false,
                        'after' => 'feature'
                    ]
                ),
                new Column(
                    'event_on',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => false,
                        'after' => 'rating'
                    ]
                ),
                new Column(
                    'event_length',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 10,
                        'after' => 'event_on'
                    ]
                ),
                new Column(
                    'event_location',
                    [
                        'type' => Column::TYPE_TINYTEXT,
                        'notNull' => false,
                        'after' => 'event_length'
                    ]
                ),
                new Column(
                    'postcode',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 20,
                        'after' => 'event_location'
                    ]
                ),
                new Column(
                    'longitude',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'postcode'
                    ]
                ),
                new Column(
                    'latitude',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "0",
                        'notNull' => false,
                        'size' => 100,
                        'after' => 'longitude'
                    ]
                ),
                new Column(
                    'created_at',
                    [
                        'type' => Column::TYPE_DATETIME,
                        'notNull' => true,
                        'after' => 'latitude'
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
                new Index('pages_template_id_IDX', ['template_id'], ''),
                new Index('pages_name_IDX', ['name'], ''),
                new Index('pages_url_IDX', ['url'], ''),
                new Index('pages_status_IDX', ['status'], ''),
                new Index('pages_created_at_IDX', ['created_at'], ''),
                new Index('pages_created_by_IDX', ['created_by'], ''),
                new Index('pages_updated_at_IDX', ['updated_at'], ''),
                new Index('pages_updated_by_IDX', ['updated_by'], ''),
                new Index('pages_deleted_at_IDX', ['deleted_at'], ''),
                new Index('pages_deleted_by_IDX', ['deleted_by'], ''),
                new Index('pages_searchable_IDX', ['searchable'], ''),
                new Index('pages_sitemap_IDX', ['sitemap'], ''),
                new Index('pages_cover_image_id_IDX', ['cover_image_id'], ''),
                new Index('pages_banner_image_id_IDX', ['banner_image_id'], ''),
                new Index('pages_spinnable_IDX', ['spinnable'], ''),
                new Index('pages_spin_label_IDX', ['spin_label'], ''),
                new Index('pages_pre_spin_name_IDX', ['pre_spin_name'], ''),
                new Index('pages_last_spun_IDX', ['last_spun'], ''),
                new Index('pages_spins_IDX', ['spins'], ''),
                new Index('pages_spin_content_id_IDX', ['spin_content_id'], ''),
                new Index('pages_sort_IDX', ['sort'], ''),
                new Index('pages_feature_IDX', ['feature'], ''),
                new Index('pages_meta_keywords_IDX', ['meta_keywords'], ''),
                new Index('pages_meta_author_IDX', ['meta_author'], ''),
                new Index('pages_canonical_url_IDX', ['canonical_url'], ''),
                new Index('pages_type_IDX', ['type'], ''),
                new Index('pages_rating_IDX', ['rating'], ''),
                new Index('pages_campaign_id_IDX', ['campaign_id'], ''),
                new Index('pages_event_on_IDX', ['event_on'], ''),
                new Index('pages_event_location_IDX', ['event_location'], ''),
                new Index('pages_parent_id_IDX', ['parent_id'], ''),
                new Index('pages_event_postcode_IDX', ['postcode'], ''),
                new Index('pages_longitude_IDX', ['longitude'], ''),
                new Index('pages_latitude_IDX', ['latitude'], ''),
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
