<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class UmiBoardColumnsMigration_104
 */
class UmiBoardColumnsMigration_104 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('umi_board_columns', [
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
                    'board_id',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'sort',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'default' => "0",
                        'notNull' => true,
                        'size' => 11,
                        'after' => 'board_id'
                    ]
                ),
                new Column(
                    'name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'sort'
                    ]
                ),
                new Column(
                    'entry_status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "active",
                        'notNull' => true,
                        'size' => 20,
                        'after' => 'name'
                    ]
                ),
                new Column(
                    'created_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'entry_status'
                    ]
                ),
                new Column(
                    'created_at',
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
                new Index('umi_board_columns_board_id_IDX', ['board_id'], ''),
                new Index('umi_board_columns_created_at_IDX', ['created_at'], ''),
                new Index('umi_board_columns_created_by_IDX', ['created_by'], ''),
                new Index('umi_board_columns_deleted_at_IDX', ['deleted_at'], ''),
                new Index('umi_board_columns_deleted_by_IDX', ['deleted_by'], ''),
                new Index('umi_board_columns_id_IDX', ['id'], ''),
                new Index('umi_board_columns_sort_IDX', ['sort'], ''),
                new Index('umi_board_columns_updated_at_IDX', ['updated_at'], ''),
                new Index('umi_board_columns_updated_by_IDX', ['updated_by'], ''),
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
