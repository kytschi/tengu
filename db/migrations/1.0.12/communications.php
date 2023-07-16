<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class CommunicationsMigration_112
 */
class CommunicationsMigration_112 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('communications', [
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
                    'box',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "inbox",
                        'notNull' => true,
                        'size' => 10,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'type',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "email",
                        'notNull' => true,
                        'size' => 50,
                        'after' => 'box'
                    ]
                ),
                new Column(
                    'subject',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'type'
                    ]
                ),
                new Column(
                    'message',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => true,
                        'after' => 'subject'
                    ]
                ),
                new Column(
                    'from_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'message'
                    ]
                ),
                new Column(
                    'from_email',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'from_name'
                    ]
                ),
                new Column(
                    'from_phone',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'from_email'
                    ]
                ),
                new Column(
                    'to_name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'from_phone'
                    ]
                ),
                new Column(
                    'to_email',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'to_name'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "sending",
                        'notNull' => true,
                        'size' => 50,
                        'after' => 'to_email'
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
                new Index('communications_id_IDX', ['id'], ''),
                new Index('communications_type_IDX', ['type'], ''),
                new Index('communications_subject_IDX', ['subject'], ''),
                new Index('communications_from_name_IDX', ['from_name'], ''),
                new Index('communications_from_email_IDX', ['from_email'], ''),
                new Index('communications_to_name_IDX', ['to_name'], ''),
                new Index('communications_to_email_IDX', ['to_email'], ''),
                new Index('communications_status_IDX', ['status'], ''),
                new Index('communications_created_at_IDX', ['created_at'], ''),
                new Index('communications_created_by_IDX', ['created_by'], ''),
                new Index('communications_updated_at_IDX', ['updated_at'], ''),
                new Index('communications_updated_by_IDX', ['updated_by'], ''),
                new Index('communications_deleted_at_IDX', ['deleted_at'], ''),
                new Index('communications_deleted_by_IDX', ['deleted_by'], ''),
                new Index('communications_from_phone_IDX', ['from_phone'], ''),
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
