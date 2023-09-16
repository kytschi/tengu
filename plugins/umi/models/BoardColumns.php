<?php

/**
 * Board columns model.
 *
 * @package     Kytschi\Umi\Models\BoardColumns
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Umi\Models;

use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Umi\Models\BoardEntries;
use Kytschi\Umi\Models\Boards;

class BoardColumns extends Model
{
    public $board_id;
    public $sort;
    public $name;
    public $entry_status = 'active';

    public function initialize()
    {
        $this->setSource('umi_board_columns');

        $this->hasOne(
            'updated_by',
            Users::class,
            'id',
            [
                'alias'    => 'updated',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'created_by',
            Users::class,
            'id',
            [
                'alias'    => 'created',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'deleted_by',
            Users::class,
            'id',
            [
                'alias'    => 'deleted',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'board_id',
            Boards::class,
            'id',
            [
                'alias'    => 'board',
                'reusable' => true
            ]
        );

        $this->hasMany(
            'id',
            BoardEntries::class,
            'board_column_id',
            [
                'alias'    => 'entries',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'sort'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Logs::class,
            'resource_id',
            [
                'alias'    => 'logs',
                'reusable' => true,
                'params'   => [
                    'order' => 'created_at DeSC'
                ]
            ]
        );
    }
}
