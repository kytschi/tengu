<?php

/**
 * Page Categories traits.
 *
 * @package     Kytschi\Tengu\Traits\Website\PageCategories
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

namespace Kytschi\Tengu\Traits\Website;

use Kytschi\Tengu\Models\Website\Pages;

trait PageCategories
{
    public function findCategory($data)
    {
        if (empty($data)) {
            return null;
        }

        if (empty($data['category'])) {
            return null;
        }

        $binds = ['id' => $data['category']];

        return Pages::find([
            'conditions' => 'id = :id:',
            'bind' => $binds
        ]);
    }
}
