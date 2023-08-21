<?php

/**
 * Export model.
 *
 * @package     Kytschi\Makabe\Models\Exports
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

namespace Kytschi\Makabe\Models;

use Kytschi\Makabe\Models\SpinContent;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Queue;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;

class Exports extends Model
{
    public $name;
    public $spin_content_id;
    public $resource;
    public $resource_id;

    public function initialize()
    {
        $this->setSource('makabe_exports');

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
            'spin_content_id',
            SpinContent::class,
            'id',
            [
                'alias'    => 'spin',
                'reusable' => true
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

        $this->hasOne(
            'id',
            Queue::class,
            'resource_id',
            [
                'params'   => [
                    'conditions' => 'status IN ("pending", "running") AND deleted_at IS NULL',
                ],
                'alias'    => 'job',
                'reusable' => true
            ]
        );
    }
}
