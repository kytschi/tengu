<?php

/**
 * Projects model.
 *
 * @package     Kytschi\Umi\Models\Projects
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Umi\Models;

use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Projects as Model;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Umi\Models\Boards;
use Kytschi\Wako\Models\StatementItems;

class Projects extends Model
{
    public function initialize()
    {
        parent::initialize();
        
        $this->hasOne(
            'id',
            Boards::class,
            'project_id',
            [
                'alias'    => 'board',
                'reusable' => true,
            ]
        );

        $this->hasMany(
            'id',
            StatementItems::class,
            'project_id',
            [
                'alias'    => 'statement_items',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );
    }
}
