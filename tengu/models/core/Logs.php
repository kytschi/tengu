<?php

/**
 * Logs model.
 *
 * @package     Kytschi\Tengu\Models\Core\Logs
 * @copyright   2021 Kytschi
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

use Kytschi\Tengu\Models\Model;

class Logs extends Model
{
    public $resource;
    public $resource_id;
    public $summary;
    public $content;
    public $type = 'info';

    public function initialize()
    {
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
    }

    public function getResourceUrl()
    {
        $url = '';

        if ($this->resource_id) {
            switch ($this->resource) {
                case 'blog-post':
                    $url = ($this->di->getConfig())->urls->cms . '/blog-posts/edit/' . $this->resource_id;
                    break;
                case 'page':
                    $url = ($this->di->getConfig())->urls->cms . '/pages/edit/' . $this->resource_id;
                    break;
                case 'portfolio':
                    $url = ($this->di->getConfig())->urls->cms . '/portfolio/edit/' . $this->resource_id;
                    break;
            }
        }

        return $url;
    }
}
