<?php

/**
 * Filters traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Filters
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

namespace Kytschi\Tengu\Traits\Core;

use Kytschi\Tengu\Controllers\ControllerBase;

trait Filters
{
    public function getFilters($key = '', $default = '')
    {
        $this->setFilters();
        
        if (empty($this->filters)) {
            return null;
        }
                
        if (is_string($key) && !empty($this->filters)) {
            return !empty($this->filters[$key]) ? $this->filters[$key] : $default;
        }

        return $this->filters;
    }

    public function setFilters()
    {
        if (!empty($_GET['filters'])) {
            if (is_array($_GET['filters'])) {
                $this->filters = $_GET['filters'];
            } else {
                $splits = explode('_', $_GET['filters']);
                $label = $splits[0];
                unset($splits[0]);
                $this->filters[$label] = implode(' ', $splits);
            }
        }
    }
}
