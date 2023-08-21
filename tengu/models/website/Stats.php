<?php

/**
 * Stats model.
 *
 * @package     Kytschi\Tengu\Models\Website\Stats
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

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Pages;

class Stats extends Model
{
    public $resource;
    public $resource_id;
    public $parent_id;
    public $visitor;
    public $referer;
    public $bot;
    public $agent;
    public $browser;
    public $operating_system;

    protected $encrypted = [
        'visitor'
    ];

    public function initialize()
    {
        $this->hasOne(
            'resource_id',
            Pages::class,
            'id',
            [
                'alias'    => 'page',
                'reusable' => true,
                'conditions' => 'resource="page"'
            ]
        );
    }
}
