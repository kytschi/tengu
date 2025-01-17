<?php

/**
 * Board entry model.
 *
 * @package     Kytschi\Umi\Models\BoardEntries
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Umi\Models;

use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Umi\Models\BoardColumns;

class BoardEntries extends Model
{
    public $board_column_id;
    public $assign_to;
    public $sort;
    public $title;
    public $description;
    public $due_on;
    public $status = 'active';

    public function initialize()
    {
        $this->setSource('umi_board_entries');

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
            'board_column_id',
            BoardColumns::class,
            'id',
            [
                'alias'    => 'column',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'assign_to',
            Users::class,
            'id',
            [
                'alias'    => 'assigned_to',
                'reusable' => true,
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
                    'order' => 'created_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Notes::class,
            'resource_id',
            [
                'alias'    => 'notes',
                'reusable' => true,
                'params'   => [
                    'order' => 'created_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Files::class,
            'resource_id',
            [
                'alias'    => 'attachments',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );
    }
}
