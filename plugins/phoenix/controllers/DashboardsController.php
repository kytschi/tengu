<?php

/**
 * Dashboards controller.
 *
 * @package     Kytschi\Phoenix\Controllers\DashboardsController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Phoenix\Models\Orders;

class DashboardsController extends ControllerBase
{
    public function indexAction()
    {
        $this->secure();
        $this->setPageTitle('Sales dashboard');
        $this->setFilters();

        $year = Orders::findFirst(['order' => 'created_at']);
        $years = [intval(date('Y', strtotime($year->created_at)))];
        for ($iLoop = $years[0] + 1; $iLoop <= intval(date('Y')); $iLoop++) {
            $years[] = $iLoop;
        }

        return $this->view->partial(
            'phoenix/dashboards/index',
            [
                'orders' => $this->getOrdersStats(),
                'years' => $years
            ]
        );
    }

    private function getOrdersStats()
    {
        $year = date('Y');
        if (!empty($this->filters)) {
            if (!empty($this->filters['year'])) {
                $year = $this->filters['year'];
            }
        }

        $table = (new Orders())->getSource();

        $query = 'SELECT ';
        for ($iLoop = 1; $iLoop <= 12; $iLoop++) {
            $month = $iLoop;
            if ($month < 10) {
                $month = '0' . $month;
            }

            $query .= "(SELECT count(id) FROM $table WHERE created_at BETWEEN '" . $year . "-" .
                $month . "-01' AND '" . $year . "-" .
                $month . "-31' AND status='active' AND deleted_at IS NULL) AS " .
                strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_active,";

            $query .= "(SELECT count(id) FROM $table WHERE dispatched_at BETWEEN '" . $year . "-" .
                    $month . "-01' AND '" . $year . "-" .
                    $month . "-31' AND status='dispatched' AND deleted_at IS NULL) AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_dispatched,";

            $query .= "(SELECT count(id) FROM $table WHERE created_at BETWEEN '" . $year . "-" .
                $month . "-01' AND '" . $year . "-" .
                $month . "-31' AND status='dispatch' AND deleted_at IS NULL) AS " .
                strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_dispatch,";
        }
        
        $model = new Orders();
        $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];

        $return = [];

        foreach ($results as $key => $result) {
            $splits = explode('_', $key);
            if (empty($return[$splits[1]])) {
                $return[$splits[1]] = [];
            }
            $return[$splits[1]][$splits[0]] = $result;
        }

        return $return;
    }
}
