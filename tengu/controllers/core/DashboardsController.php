<?php

/**
 * Dashboards controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\DashboardsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Models\Website\Stats;
use Kytschi\Tengu\Models\Website\StatsData;
use Kytschi\Tengu\Traits\Core\User;

class DashboardsController extends ControllerBase
{
    public function contentAction()
    {
        $this->secure();

        return $this->view->partial(
            'core/dashboards/content'
        );
    }

    public function homeAction()
    {
        $this->secure();
        $this->setPageTitle('Dashboard');
        $this->setFilters();

        $stat = Stats::findFirst(['order' => 'created_at']);
        if ($stat) {
            $years = [intval(date('Y', strtotime($stat->created_at)))];
        } else {
            $years = [intval(date('Y'))];
        }
        for ($iLoop = $years[0] + 1; $iLoop <= intval(date('Y')); $iLoop++) {
            $years[] = $iLoop;
        }

        return $this->view->partial(
            'core/dashboards/home',
            [
                'visitors' => $this->getVisitorStats(),
                'referrers' => $this->getReferrers(),
                'bots' => $this->getBots(),
                'browsers' => $this->getBrowsers(),
                'most_viewed' => $this->getMostViewed(),
                'operating_systems' => $this->getOperatingSystems(),
                'years' => $years,
                'previous' => $this->getPrevious(),
                'visitors_map' => $this->getVisitorMapData()
            ]
        );
    }

    private function getBots()
    {
        $query = "SELECT count(id) AS total, bot FROM stats WHERE bot IS NOT NULL ";
        if (!empty($this->filters)) {
            if (!empty($this->filters['year'])) {
                $query .= "AND (created_at 
                    BETWEEN '" . $this->filters['year'] . "-01-01' AND '" . $this->filters['year'] . "-12-31') ";
            }
        }
        $query .= "GROUP BY bot ORDER BY total DESC";
        $model = new Stats();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray();
    }

    private function getBrowsers()
    {
        $query = "SELECT count(id) AS total, browser FROM stats WHERE browser IS NOT NULL ";
        if (!empty($this->filters)) {
            if (!empty($this->filters['year'])) {
                $query .= "AND (created_at 
                    BETWEEN '" . $this->filters['year'] . "-01-01' AND '" . $this->filters['year'] . "-12-31') ";
            }
        }
        $query .= "GROUP BY browser ORDER BY total DESC";
        $model = new Stats();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray();
    }

    private function getMostViewed()
    {
        $query = "SELECT 
            count(stats.id) AS total,
            pages.id,
            pages.name,
            pages.url,
            pages.type,
            pages.spinnable
        FROM 
            stats 
        LEFT JOIN 
            pages ON pages.id = stats.resource_id
        WHERE resource='page' AND pages.id IS NOT NULL ";
        if (!empty($this->filters)) {
            if (!empty($this->filters['year'])) {
                $query .= "AND (stats.created_at 
                    BETWEEN '" . $this->filters['year'] . "-01-01' AND '" . $this->filters['year'] . "-12-31') ";
            }
        }
        $query .= "GROUP BY stats.resource_id
        ORDER BY total DESC";
        $model = new Stats();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ));
    }

    private function getReferrers()
    {
        $query = "SELECT count(id) AS total, referer FROM stats WHERE referer IS NOT NULL ";
        if (!empty($this->filters)) {
            if (!empty($this->filters['year'])) {
                $query .= "AND (created_at 
                    BETWEEN '" . $this->filters['year'] . "-01-01' AND '" . $this->filters['year'] . "-12-31') ";
            }
        }
        $query .= "GROUP BY referer ORDER BY total DESC";
        $model = new Stats();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray();
    }

    private function getOperatingSystems()
    {
        $query = "SELECT count(id) AS total, operating_system FROM stats WHERE operating_system IS NOT NULL ";
        if (!empty($this->filters)) {
            if (!empty($this->filters['year'])) {
                $query .= "AND (created_at 
                    BETWEEN '" . $this->filters['year'] . "-01-01' AND '" . $this->filters['year'] . "-12-31') ";
            }
        }
        $query .= "GROUP BY operating_system ORDER BY total DESC";
        $model = new Stats();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model
                ->getReadConnection()
                ->query(
                    rtrim($query, ',')
                )
        ))->toArray();
    }

    private function getPrevious()
    {
        $last_month = date('Y-m', strtotime("-1 month"));
        $before_last = date('Y-m', strtotime("-2 month"));

        $query = "SELECT (SELECT count(id) FROM stats 
            WHERE created_at BETWEEN '" . $last_month . "-01' AND '" . $last_month . "-31') AS last_month,
            (SELECT count(id) FROM stats 
            WHERE created_at BETWEEN '" . $before_last . "-01' AND '" . $before_last . "-31') AS before_last";
        $model = new Stats();
        $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray();

        if ($results) {
            return $results[0];
        }

        return [
            'last_month' => 0,
            'before_last' => 0
        ];
    }

    private function getVisitorMapData()
    {
        return Stats::find([
            'columns' => 'latitude, longitude',
            'conditions' => 'latitude IS NOT NULL AND longitude IS NOT NULL',
            'group' => 'visitor'
        ]);
    }

    private function getVisitorStats()
    {
        $year = date('Y');
        if (!empty($this->filters)) {
            if (!empty($this->filters['year'])) {
                $year = $this->filters['year'];
            }
        }

        $query = 'SELECT ';
        for ($iLoop = 1; $iLoop <= 12; $iLoop++) {
            $month = $iLoop;
            if ($month < 10) {
                $month = '0' . $month;
            }

            $query .= "(SELECT count(id) FROM stats WHERE created_at BETWEEN '" . $year . "-" .
                    $month . "-01' AND '" . $year . "-" .
                    $month . "-31') AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_visitors,";

            $query .= "(SELECT count(*) FROM (SELECT count(id) FROM stats WHERE bot IS NULL AND 
                    created_at BETWEEN '" . $year . "-" .
                    $month . "-01' AND '" . $year . "-" .
                    $month . "-31' GROUP BY visitor) AS total) AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_unique,";

            $query .= "(SELECT count(id) FROM stats WHERE bot IS NOT NULL AND 
                    created_at BETWEEN '" . $year . "-" . $month . "-01' AND '" . $year . "-" .
                    $month . "-31') AS " .
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
}
