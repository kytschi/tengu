<?php

/**
 * Dashboards controller.
 *
 * @package     Kytschi\Mai\Controllers\DashboardsController
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
