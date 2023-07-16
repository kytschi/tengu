<?php

/**
 * Dashboards controller.
 *
 * @package     Kytschi\Mai\Controllers\DashboardsController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Mai\Controllers;

use Kytschi\Mai\Models\TimesheetEntries;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Helpers\DateHelper;

class DashboardsController extends ControllerBase
{
    public function indexAction()
    {
        $this->secure();
        $this->setPageTitle('Dashboard');

        return $this->view->partial(
            'mai/dashboards/index',
            [
                'data' => $this->stats()
            ]
        );
    }

    public function stats()
    {
        $stats = [
            'hours_month' => 0
        ];

        if (
            $results  = TimesheetEntries::find(
                [
                    'conditions' => '
                        deleted_at IS NULL AND
                        started_at BETWEEN "' . date('Y-m') . '-01" AND "' . date('Y-m') . '-31" AND 
                        ended_at BETWEEN "' . date('Y-m') . '-01" AND "' . date('Y-m') . '-31"'
                ]
            )
        ) {
            foreach ($results as $result) {
                $stats['hours_month'] += DateHelper::numberOfHours($result->started_at, $result->ended_at);
            }
        }
        
        return $stats;
    }
}
