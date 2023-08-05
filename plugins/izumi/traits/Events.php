<?php

/**
 * Events traits.
 *
 * @package     Kytschi\Izumi\Traits\Events
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

namespace Kytschi\Izumi\Traits;

use Kytschi\Tengu\Models\Website\Pages;

trait Events
{
    public function findEvents($data = [])
    {
        $query = '';
        $bind = [];

        if (!empty($data)) {
            if (!empty($data['where'])) {
                if (!empty($data['where']['query'])) {
                    $query = 'AND ' . $data['where']['query'];
                }

                if (!empty($data['where']['data'])) {
                    $bind = $data['where']['data'];
                }
            }
        }

        return Pages::find([
            'conditions' => 'type = "event" AND deleted_at IS NULL ' . $query,
            'bind' => $bind
        ]);
    }

    public function findFeaturedEvents($data = [])
    {
        $query = '';
        $binds = [];

        if (!empty($data['random'])) {
            $order = 'RAND() LIMIT ' . intval($data['random']);
        } else {
            $order = 'created_at';
        }

        return Pages::find([
            'conditions' => 'type in ("event", "event-category") AND feature = 1 AND deleted_at IS NULL ' . $query,
            'bind' => $binds,
            'order' => $order
        ]);
    }
}
