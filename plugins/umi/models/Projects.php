<?php

/**
 * Projects model.
 *
 * @package     Kytschi\Umi\Models\Projects
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301, USA.
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
