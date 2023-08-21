<?php

/**
 * Spun content model.
 *
 * @package     Kytschi\Makabe\Models\SpunContent
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
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Website\Stats;

class SpunContent extends Model
{
    public $spin_content_id;
    public $resource_id;
    public $resource;
    public $url;
    public $canonical_url;
    public $name;
    public $content;
    public $meta_keywords;
    public $meta_description;
    public $sort = 1;
    public $used_at;

    public function initialize()
    {
        $this->setSource('makabe_spun_content');

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
            'spin_content_id',
            SpinContent::class,
            'id',
            [
                'alias'    => 'spin_content',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'resource_id',
            Pages::class,
            'id',
            [
                'alias'    => 'page',
                'reusable' => true,
            ]
        );
    }

    public function getBots()
    {
        $query = "SELECT count(id) AS total, bot FROM stats 
            WHERE bot IS NOT NULL AND resource_id = '" . $this->id . "' GROUP BY bot";
        $model = new Stats();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray();
    }

    public function getReferrers()
    {
        $query = "SELECT count(id) AS total, referer FROM stats WHERE referer IS NOT NULL 
            AND resource_id = '" . $this->id . "' GROUP BY referer";
        $model = new Stats();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray();
    }

    public function getResourceType()
    {
        return 'spun-content';
    }

    public function getStats($year = '')
    {
        if (empty($year)) {
            $year = date('Y');
        }

        $table = (new Stats())->getSource();
        
        $query = 'SELECT ';
        for ($iLoop = 1; $iLoop <= 12; $iLoop++) {
            $month = $iLoop;
            if ($month < 10) {
                $month = '0' . $month;
            }

            $query .= "(SELECT count(id) FROM $table WHERE created_at BETWEEN '" . $year . "-" .
                    $month . "-01' AND '" . $year . "-" .
                    $month . "-31' AND resource_id = '" . $this->id . "') AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_visitors,";

            $query .= "(SELECT count(*) FROM (SELECT count(id) FROM $table WHERE created_at BETWEEN '" . $year . "-" .
                    $month . "-01' AND '" . $year . "-" .
                    $month . "-31' AND resource_id = '" . $this->id . "' GROUP BY visitor) AS total) AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_unique,";

            $query .= "(SELECT count(id) FROM stats WHERE bot IS NOT NULL AND 
                    created_at BETWEEN '" . $year . "-" . $month . "-01' AND '" . $year . "-" .
                    $month . "-31' AND resource_id = '" . $this->id . "') AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_bot,";
        }

        $query .= "(SELECT count(id) FROM $table WHERE resource_id = '" . $this->id . "') AS total,";

        $query .= "(SELECT count(id) FROM " . $this->getSource() . " WHERE used_at IS NOT NULL) AS used,";

        $model = new Stats();
        $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];

        $return = [];

        foreach ($results as $key => $result) {
            $splits = explode('_', $key);
            if (count($splits) == 1) {
                $return[$splits[0]] = $result;
            } else {
                if (empty($return[$splits[1]])) {
                    $return[$splits[1]] = [];
                }

                $return[$splits[1]][$splits[0]] = $result;
            }
        }

        return $return;
    }
}
