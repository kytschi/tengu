<?php

/**
 * Timesheets model.
 *
 * @package     Kytschi\Mai\Models\Timesheets
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

namespace Kytschi\Mai\Models;

use Kytschi\Mai\Models\TimesheetEntries;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Projects;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;

class Timesheets extends Model
{
    public $project_id = null;
    public $name;
    public $summary = null;
    public $amount = 0;
    public $period_start = null;
    public $period_end = null;
    public $search_tags = null;
    public $status = 'active';

    public function initialize()
    {
        $this->setSource('mai_timesheets');

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

        $this->hasMany(
            'id',
            TimesheetEntries::class,
            'timesheet_id',
            [
                'alias'    => 'entries',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'started_at'
                ]
            ]
        );

        $this->hasOne(
            'project_id',
            Projects::class,
            'id',
            [
                'alias'    => 'project',
                'reusable' => true
            ]
        );

        $this->hasMany(
            'id',
            Tags::class,
            'resource_id',
            [
                'alias'    => 'tags',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'tag'
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
    }

    public function getPeriod()
    {
        $return = '';
        if ($this->period_start) {
            $return = DateHelper::pretty($this->period_start, false);
        }

        if ($this->period_end) {
            if ($this->period_start) {
                $return .= ' to ';
            }

            $return .= DateHelper::pretty($this->period_end, false);
        }

        return $return;
    }

    public function getTotal()
    {
        $model = new TimesheetEntries();
        $query = 'SELECT SUM(price) AS total FROM ' . $model->getSource() . ' 
        WHERE timesheet_id = "' . $this->id . '" AND deleted_at IS NULL';
        
        if (
            $result = (new \Phalcon\Mvc\Model\Resultset\Simple(
                null,
                $model,
                $model->getReadConnection()->query($query)
            ))
        ) {
            return $result->toArray()[0]['total'];
        }

        return 0;
    }
}
