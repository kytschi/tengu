<?php

/**
 * Spin content model.
 *
 * @package     Kytschi\Makabe\Models\SpinContent
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Makabe\Models;

use Kytschi\Makabe\Models\Campaigns;
use Kytschi\Makabe\Models\Keywords;
use Kytschi\Makabe\Models\SpinContentKeywords;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Queue;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Website\Stats;

class SpinContent extends Model
{
    public $resource;
    public $resource_id;
    public $campaign_id;
    public $label;
    public $content;
    public $name;
    public $url;
    public $canonical_url;
    public $meta_keywords;
    public $meta_description;

    public function initialize()
    {
        $this->setSource('makabe_spin_content');

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
            'campaign_id',
            Campaigns::class,
            'id',
            [
                'alias'    => 'campaign',
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
            'resource_id',
            Pages::class,
            'id',
            [
                'alias'    => 'page',
                'reusable' => true,
            ]
        );

        $this->hasManyToMany(
            'id',
            SpinContentKeywords::class,
            'spin_content_id',
            'keyword_id',
            Keywords::class,
            'id',
            [
                'alias'    => 'keywords',
                'reusable' => true,
                'params'   => [
                    'conditions' => SpinContentKeywords::class . '.deleted_at IS NULL',
                    'order' => 'rank DESC'
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
                    $month . "-31' AND parent_id = '" . $this->id . "') AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_visitors,";

            $query .= "(SELECT count(*) FROM (SELECT count(id) FROM $table WHERE created_at BETWEEN '" . $year . "-" .
                    $month . "-01' AND '" . $year . "-" .
                    $month . "-31' AND parent_id = '" . $this->id . "' GROUP BY visitor) AS total) AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_unique,";

            $query .= "(SELECT count(id) FROM stats WHERE bot IS NOT NULL AND 
                    created_at BETWEEN '" . $year . "-" . $month . "-01' AND '" . $year . "-" .
                    $month . "-31' AND resource_id = '" . $this->id . "') AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_bot,";
        }

        $query .= "(SELECT count(id) FROM $table WHERE parent_id = '" . $this->id . "') AS total,";

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

    public function getSpins()
    {
        $table = (new SpunContent())->getSource();

        $query = "SELECT COUNT(id) AS total FROM $table WHERE used_at IS NOT NULL AND deleted_at IS NULL";

        $model = new self();
        $count = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray();

        return ($count) ? intval($count[0]['total']) : 0;
    }

    public function getSpunCount()
    {
        $model = new self();
        $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(
                'SELECT COUNT(id) AS total FROM ' . (new SpunContent())->getSource() .
                ' WHERE spin_content_id = "' . $this->id . '" AND deleted_at IS NULL'
            )
        ))->toArray();

        if ($results) {
            return $results[0]['total'];
        }

        return 0;
    }

    public function getSpunUsedAt()
    {
        $model = new Stats();
        $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(
                'SELECT 
                    used_at 
                FROM ' . (new SpunContent())->getSource() . ' 
                WHERE 
                    spin_content_id = "' . $this->id . '" AND 
                    deleted_at IS NULL
                ORDER BY used_at DESC
                LIMIT 1'
            )
        ))->toArray();

        if ($results) {
            return $results[0]['used_at'];
        }

        return null;
    }
}
