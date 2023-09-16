<?php

/**
 * Keywords model.
 *
 * @package     Kytschi\Makabe\Models\Keywords
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Makabe\Models;

use Kytschi\Makabe\Controllers\PageScannerController;
use Kytschi\Makabe\Models\ScanPages;
use Kytschi\Makabe\Models\SpinContent;
use Kytschi\Makabe\Models\SpunContent;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Website\SearchStats;
use Kytschi\Tengu\Models\Website\Stats;
use Kytschi\Tengu\Models\Model;

class Keywords extends Model
{
    public $campaign_id;
    public $rank = 0;
    public $resource;
    public $resource_id;
    public $keyword;
    public $case_sensitive = true;

    public function initialize()
    {
        $this->setSource('makabe_keywords');

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
                    'order' => 'created_at DeSC'
                ]
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

    public function getPopularity()
    {
        if ($this->rank < 10) {
            return 'Not very';
        } elseif ($this->rank < 20) {
            return 'Pretty popular';
        } elseif ($this->rank < 30) {
            return 'Somewhat popular';
        } elseif ($this->rank < 40) {
            return 'Popular';
        } elseif ($this->rank < 50) {
            return 'Very popular';
        } else {
            return 'Extremely popular';
        }
    }

    public function getResourceType()
    {
        return 'keyword';
    }

    public function getDashboardStats()
    {
        $table = (new SearchStats())->getSource();
        $query = "SELECT (
            SELECT SUM(search_stats.total)
            FROM (
                SELECT count(id) AS total
                FROM $table 
                WHERE
                    query REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' AND 
                    deleted_at IS NULL GROUP BY query, visitor
            ) AS search_stats
        ) AS search,";

        $table = (new Stats())->getSource();
        $query .= "(
            SELECT SUM(stats.total)
            FROM (
                SELECT 
                    count(id) AS total
                FROM $table 
                WHERE resource_id='" . $this->id . "' GROUP BY visitor
            ) AS stats
        ) AS visitors";

        $model = new SearchStats();
        $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray();

        if ($results) {
            return $results[0];
        }

        return ['search' => 0, 'visitors' => 0];
    }
   
    public function getStats($year = '')
    {
        if (empty($year)) {
            $year = date('Y');
        }

        $table = (new Stats())->getSource();
        $search_table = (new SearchStats())->getSource();
        
        $query = 'SELECT ';
        for ($iLoop = 1; $iLoop <= 12; $iLoop++) {
            $month = $iLoop;
            if ($month < 10) {
                $month = '0' . $month;
            }

            $query .= "(SELECT count(*) FROM (SELECT count(id) FROM $table WHERE created_at BETWEEN '" . $year . "-" .
                $month . "-01' AND '" . $year . "-" .
                $month . "-31' AND resource_id = '" . $this->id . "' GROUP BY visitor) AS total) AS " .
                strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_visitors,";

            $query .= "(SELECT count(*) FROM (SELECT count(id) FROM $search_table WHERE created_at BETWEEN '" . $year . "-" .
                $month . "-01' AND '" . $year . "-" .
                $month . "-31' AND query REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' GROUP BY visitor) AS total) AS " .
                strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_search,";

            $query .= "(SELECT count(id) FROM stats WHERE bot IS NOT NULL AND 
                    created_at BETWEEN '" . $year . "-" . $month . "-01' AND '" . $year . "-" .
                    $month . "-31' AND resource_id = '" . $this->id . "') AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_bot,";
        }

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

    public function getUsedIn()
    {
        //Spin content
        $table = (new SpinContent())->getSource();
        $sql = "SELECT
                resource AS type,
                'spin content' AS sub_type,
                label as name,
                CASE resource
                    WHEN 'blog-post' THEN CONCAT('/cms/blog-posts/', resource_id, '/spinner')
                    WHEN 'portfolio' THEN CONCAT('/cms/portfolio/', resource_id, '/spinner')
                    ELSE CONCAT('/cms/pages/', resource_id, '/spinner')
                END AS url
            FROM $table
            WHERE
                (
                    content REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    url REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    name REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    meta_keywords REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    meta_description REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]'
                )
                AND deleted_at IS NULL ";

        //Pages
        $sql .= "UNION 
            SELECT
                type,
                '' AS sub_type,
                name,
                CASE type
                    WHEN 'blog-post' THEN CONCAT('/cms/blog-posts/edit/', id)
                    WHEN 'portfolio' THEN CONCAT('/cms/portfolio', id)
                    ELSE CONCAT('/cms/pages', id)
                END AS url
            FROM " . (new Pages())->getSource() . "
            WHERE
                (
                    pre_spin_content REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    url REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    name REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    meta_description REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]'
                )
                AND deleted_at IS NULL";

        $pages = new Pages();
        $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $pages,
            $pages
                ->getReadConnection()
                ->query($sql)
        ))->toArray();

        //Scanned pages
        if (
            !empty(
                $pages = ScanPages::find(
                    [
                        'binds' => 'deleted_at IS NULL'
                    ]
                )
            )
        ) {
            $controller = new PageScannerController();
            foreach ($pages as $page) {
                $overview = $controller->getOverview($page);
                if (empty($overview)) {
                    continue;
                }
                $found = false;

                $obj = [];
                $obj['type'] = 'scanned page';
                $obj['sub_type'] = '';
                $obj['name'] = $page->name;
                $obj['url'] = '/mms/page-scanner/' . $page->id;

                if (str_contains($overview['title'], $this->keyword)) {
                    $results[] = $obj;
                    continue;
                }

                foreach ($overview['h_tags'] as $tag) {
                    if (str_contains($tag['text'], $this->keyword)) {
                        $results[] = $obj;
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    continue;
                }

                foreach ($overview['meta'] as $tag) {
                    if (str_contains($tag['content'], $this->keyword)) {
                        $results[] = $obj;
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    continue;
                }

                foreach ($overview['text'] as $tag) {
                    if (str_contains($tag['text'], $this->keyword)) {
                        $results[] = $obj;
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    continue;
                }
            }
        }

        return $results;
    }

    public function getUsedInCount()
    {
        //Spun content
        $sql = "SELECT
                COUNT(id) AS total
            FROM " . (new SpinContent())->getSource() . "
            WHERE
                (
                    content REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    url REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    name REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    meta_keywords REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    meta_description REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]'
                )
                AND deleted_at IS NULL ";

        //Pages
        $sql .= "UNION
            SELECT
                COUNT(id) AS total
            FROM " . (new Pages())->getSource() . "
            WHERE
                (
                    pre_spin_content REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    url REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    name REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR
                    meta_keywords REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]' OR 
                    meta_description REGEXP '[[:<:]]" . addslashes($this->keyword) . "[[:>:]]'
                )
                AND deleted_at IS NULL";

        $pages = new Pages();
        $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $pages,
            $pages
                ->getReadConnection()
                ->query($sql)
        ))->toArray();

        if ($results) {
            $total = 0;
            foreach ($results as $result) {
                $total += intval($result['total']);
            }
            return $total;
        }

        return 0;
    }
}
