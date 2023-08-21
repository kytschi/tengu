<?php

/**
 * Queue model.
 *
 * @package     Kytschi\Tengu\Models\Core\Queue
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

namespace Kytschi\Tengu\Models\Core;

use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Model;

class Queue extends Model
{
    public $priority = 1;
    public $resource;
    public $resource_id;
    public $job;
    public $status = 'pending';
    public $completed_at;
    public $started_at;

    public function initialize()
    {
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

    public function getJobName()
    {
        $splits = explode('\\', $this->job);
        $words = preg_split('/(?=[A-Z])/', $splits[count($splits) - 1]);
        return implode(' ', $words);
    }

    public function getPriorityString()
    {
        if ($this->priority == 1) {
            return 'normal';
        } elseif ($this->priority == 2) {
            return 'low';
        } elseif ($this->priority == 0) {
            return 'high';
        }
    }
}
