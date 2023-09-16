<?php

/**
 * Dashboards controller.
 *
 * @package     Kytschi\Makabe\Controllers\DashboardsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Makabe\Controllers;

use Kytschi\Makabe\Models\Keywords;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Models\Website\SearchStats;
use Kytschi\Tengu\Models\Website\Stats;

class DashboardsController extends ControllerBase
{
    public function indexAction()
    {
        $this->secure();
        $this->setPageTitle('Dashboard');

        return $this->view->partial(
            'makabe/dashboards/index',
            [
                'keywords' => Keywords::find(['bind' => 'deleted_at IS NULL'])
            ]
        );
    }

    public function stats()
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
                $month . "-31' AND resource = 'keyword' GROUP BY visitor) AS total) AS " .
                strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_visitors,";
/*
            $query .= "(SELECT count(*) FROM (SELECT count(id) FROM $search_table WHERE created_at BETWEEN '" . $year . "-" .
                $month . "-01' AND '" . $year . "-" .
                $month . "-31' AND query REGEXP '[[:<:]]" . $this->keyword . "[[:>:]]' GROUP BY visitor) AS total) AS " .
                strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_search,";
                */
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
