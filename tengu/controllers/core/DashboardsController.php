<?php

/**
 * Dashboards controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\DashboardsController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Models\Website\Stats;
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
        $years = [intval(date('Y', strtotime($stat->created_at)))];
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
                'years' => $years
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
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray();
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
