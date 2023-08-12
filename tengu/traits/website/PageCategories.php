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

use Kytschi\Tengu\Controllers\Core\PostcodesController;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Website\PageCategories as Model;

trait PageCategories
{
    public function findCategory($data)
    {
        if (empty($data)) {
            return null;
        }

        if (empty($data['id'])) {
            return null;
        }

        $binds = ['id' => $data['id']];

        return Pages::find([
            'conditions' => 'id = :id:',
            'bind' => $binds
        ]);
    }

    public function findCategoryItems($data)
    {
        if (empty($data)) {
            return null;
        }

        if (empty($data['category_id'])) {
            return null;
        }

        $binds = ['category_id' => $data['category_id']];

        $model = new Pages();
        $pages_table = $model->getSource();
        $cats_table = (new Model())->getSource();

        $selects = "$pages_table.*";
        $wheres = '';
        $order = "ORDER BY $cats_table.sort ASC, $cats_table.created_at DESC";
        if (!empty($data['order'])) {
            $order = $data['order'];
        }

        if (!empty($data['search'])) {
            $controller = new PostcodesController();
            if ($postcodes = $controller->hasPostcode($data['search'])) {
                if (!empty($data['radius'])) {
                    $postcode = reset($postcodes);
                    $coords = $controller->getCoordinates($postcode);
                    $latitude = $coords['latitude'];
                    $longitude = $coords['longitude'];
                    $selects .= ", 69.0 *
                        DEGREES(ACOS(LEAST(1.0, COS(RADIANS($latitude))
                                * COS(RADIANS(latitude))
                                * COS(RADIANS($longitude - longitude))
                                + SIN(RADIANS($latitude))
                                * SIN(RADIANS(latitude))))) AS distance ";

                    $order = ' HAVING distance < ' . $data['radius'] . ' ORDER BY distance ASC';
                } else {
                    $wheres = ' AND (';
                    foreach ($postcodes as $key => $postcode) {
                        $wheres .= 'postcode = :postcode_' . $key . '_1 OR postcode = :postcode_' . $key . '_2 OR ';
                        $binds['postcode_' . $key . '_1'] = $postcode;
                        $binds['postcode_' . $key . '_2'] = str_replace(' ', '', $postcode);
                    }
                    $wheres = rtrim($wheres, ' OR ') . ')';
                }
            }
        }

        $query = "SELECT
            $selects
        FROM 
            $cats_table 
        LEFT JOIN $pages_table ON $pages_table.id = $cats_table.page_id AND $pages_table.deleted_at IS NULL
        WHERE 
            category_id = :category_id AND $cats_table.deleted_at IS NULL AND $pages_table.id IS NOT NULL 
            $wheres
        $order";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model
                ->getReadConnection()
                ->query(
                    rtrim($query, ','),
                    $binds
                )
        ));
    }
}
