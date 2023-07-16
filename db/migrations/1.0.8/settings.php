<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class SettingsMigration_108
 */
class SettingsMigration_108 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('settings', [
            'columns' => [
                new Column(
                    'name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'first' => true
                    ]
                ),
                new Column(
                    'slogan',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'name'
                    ]
                ),
                new Column(
                    'address',
                    [
                        'type' => Column::TYPE_TINYTEXT,
                        'notNull' => false,
                        'after' => 'slogan'
                    ]
                ),
                new Column(
                    'meta_description',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'address'
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
                    'contact_email',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'meta_author'
                    ]
                ),
                new Column(
                    'robots_txt',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'contact_email'
                    ]
                ),
                new Column(
                    'robots',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'robots_txt'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "online",
                        'notNull' => true,
                        'size' => 50,
                        'after' => 'robots'
                    ]
                ),
                new Column(
                    'tengu_theme',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "default",
                        'notNull' => true,
                        'size' => 50,
                        'after' => 'status'
                    ]
                ),
            ],
            'indexes' => [
                new Index('PRIMARY', ['name'], 'PRIMARY'),
                new Index('settings_name_IDX', ['name'], ''),
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
