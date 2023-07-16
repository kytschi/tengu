<?php

/**
 * Campaigns model.
 *
 * @package     Kytschi\Makabe\Models\Campaigns
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Makabe\Models;

use Kytschi\Makabe\Models\ScanPages;
use Kytschi\Makabe\Models\Words;
use Kytschi\Makabe\Traits\SearchEngines;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Queue;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Stats;

class Campaigns extends Model
{
    use SearchEngines;

    public $search_engine_id;
    public $name;
    public $type = 'seo';
    public $search_terms;
    public $search_engine_html;
    public $search_engine_last_scanned;
    public $status = 'active';

    public function initialize()
    {
        $this->setSource('makabe_campaigns');

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
            ScanPages::class,
            'campaign_id',
            [
                'alias'    => 'scan_pages',
                'reusable' => true,
                'params'   => [
                    'order' => 'rank',
                    'conditions' => 'deleted_at IS NULL'
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
            WHERE bot IS NOT NULL AND resource_id = '" . $this->id . "' GROUP BY bot ORDER BY total DESC";
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

    public function getSearchEngine()
    {
        $engine = new \stdClass();

        foreach ($this->search_engines as $result) {
            if ($result['id'] != $this->search_engine_id) {
                continue;
            }

            $engine->id = $result['id'];
            $engine->name = $result['name'];
            $engine->url = $result['url'];
        }

        return $engine;
    }

    public function getSearchEngineUrl()
    {
        switch (strtolower($this->search_engine->name)) {
            case 'google':
                return $this->search_engine->url . '?num=100&q=' . urlencode($this->search_terms);
                break;
            case 'google uk':
                return $this->search_engine->url . '?num=100&q=' . urlencode($this->search_terms);
                break;
            default:
                return '';
                break;
        }
    }

    public function getStats($year = '')
    {
        if (empty($year)) {
            $year = date('Y');
        }

        $model = new Stats();
        $table = $model->getSource();

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

    public function getViews()
    {
        $stats = $this->getStats();
        return !empty($stats['total']) ? $stats['total'] : 0;
    }

    public function getWords()
    {
        $table = (new Words())->getSource();

        $query = 'SELECT id, deleted_at, word, SUM(word_count) AS word_count
        FROM ' . $table . ' 
        WHERE campaign_id = "' . $this->id . '" AND deleted_at IS NULL
        GROUP BY word 
        ORDER BY word_count DESC';
        $model = new Words();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ));
    }
}
