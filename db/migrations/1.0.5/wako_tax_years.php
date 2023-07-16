<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class WakoTaxYearsMigration_105
 */
class WakoTaxYearsMigration_105 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('wako_tax_years', [
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
                    'code',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'tax_year_start',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => true,
                        'after' => 'code'
                    ]
                ),
                new Column(
                    'tax_year_end',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => true,
                        'after' => 'tax_year_start'
                    ]
                ),
                new Column(
                    'search_tags',
                    [
                        'type' => Column::TYPE_TEXT,
                        'notNull' => false,
                        'after' => 'tax_year_end'
                    ]
                ),
                new Column(
                    'status',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'default' => "current",
                        'notNull' => true,
                        'size' => 10,
                        'after' => 'search_tags'
                    ]
                ),
                new Column(
                    'return_due_by',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => false,
                        'after' => 'status'
                    ]
                ),
                new Column(
                    'return_completed_on',
                    [
                        'type' => Column::TYPE_DATE,
                        'notNull' => false,
                        'after' => 'return_due_by'
                    ]
                ),
                new Column(
                    'created_by',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 36,
                        'after' => 'return_completed_on'
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
                        'type' => Column::TYPE_DATE,
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
                new Index('wako_tax_years_code_IDX', ['code'], ''),
                new Index('wako_tax_years_tax_year_start_IDX', ['tax_year_start'], ''),
                new Index('wako_tax_years_tax_year_end_IDX', ['tax_year_end'], ''),
                new Index('wako_tax_years_status_IDX', ['status'], ''),
                new Index('wako_tax_years_return_due_by_IDX', ['return_due_by'], ''),
                new Index('wako_tax_years_return_completed_on_IDX', ['return_completed_on'], ''),
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
