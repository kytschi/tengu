<?php

/**
 * Dashboards controller.
 *
 * @package     Kytschi\Wako\Controllers\DashboardsController
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

namespace Kytschi\Wako\Controllers;

use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Wako\Controllers\DividendsController;
use Kytschi\Wako\Controllers\InvoicesController;
use Kytschi\Wako\Models\Invoices;
use Kytschi\Wako\Models\Settings;
use Kytschi\Wako\Models\StatementItems;

class DashboardsController extends InvoicesController
{
    public $global_url = '/dashboard';
    public $resource = 'finance-dashboard';

    public function indexAction()
    {
        $this->secure();
        $this->setPageTitle('Dashboard');

        $tax_year = $this->getCurrentTaxYear();
        $users = null;
        if ($groups = (new DividendsController())->getGroups()) {
            $users = (new Users())->find([
            'conditions' =>
                'group_id IN("' . implode('","', $groups) . '")',
            ]);
        }

        return $this->view->partial(
            'wako/dashboards/index',
            [
                'directions' => $this->directions,
                'tax_year' => $tax_year,
                'stats' => $this->stats($tax_year->tax_year_start, $tax_year->tax_year_end),
                'users' => $users,
                'years' => $this->getTaxYears()
            ]
        );
    }

    public function stats($start, $end)
    {
        $stats = [
            'turnover' => 0.00,
            'turnover_taxable' => 0.00,
            'incoming' => 0.00,
            'outgoing' => 0.00,
            'incoming_taxable' => 0.00,
            'outgoing_taxable' => 0.00
        ];

        if (!$start || !$end) {
            return $stats;
        }

        $params = [
            'incoming_start' => $start,
            'incoming_end' => $end,
            'outgoing_start' => $start,
            'outgoing_end' => $end,
            'incoming_taxable_start' => $start,
            'incoming_taxable_end' => $end,
            'outgoing_taxable_start' => $start,
            'outgoing_taxable_end' => $end
        ];

        $invoice = new Invoices();
        $invoice_table = $invoice->getSource();
        $query = "SELECT 
            (SELECT
                SUM(amount) as total
            FROM 
                $invoice_table
            WHERE 
                deleted_at IS NULL AND 
                direction = 'incoming' AND 
                (issued_on BETWEEN :incoming_start AND :incoming_end)) AS incoming,
            (SELECT
                SUM(amount) as total
            FROM 
                $invoice_table
            WHERE 
                deleted_at IS NULL AND 
                direction = 'outgoing' AND 
                (issued_on BETWEEN :outgoing_start AND :outgoing_end)) AS outgoing,
            (SELECT
                SUM(amount) as total
            FROM 
                $invoice_table
            WHERE 
                deleted_at IS NULL AND 
                direction = 'incoming' AND 
                taxable = 1 AND 
                (issued_on BETWEEN :incoming_taxable_start AND :incoming_taxable_end)) AS incoming_taxable,
            (SELECT
                SUM(amount) as total
            FROM 
                $invoice_table
            WHERE 
                deleted_at IS NULL AND 
                direction = 'outgoing' AND 
                taxable = 1 AND 
                (issued_on BETWEEN :outgoing_taxable_start AND :outgoing_taxable_end)) AS outgoing_taxable";

        $result =
            (
                new \Phalcon\Mvc\Model\Resultset\Simple(
                    null,
                    $invoice,
                    $invoice->getReadConnection()->query(rtrim($query, ','), $params)
                )
            )->toArray();

        $stats['incoming'] = floatval($result[0]['incoming']);
        $stats['outgoing'] = floatval($result[0]['outgoing']);
        $stats['incoming_taxable'] = floatval($result[0]['incoming_taxable']);
        $stats['outgoing_taxable'] = floatval($result[0]['outgoing_taxable']);

        $statement_items = new StatementItems();
        $statement_items_table = $statement_items->getSource();

        $query = "SELECT
            (SELECT
                SUM(`in`) as total
            FROM 
                $statement_items_table
            WHERE 
                deleted_at IS NULL AND 
                invoice_id IS NULL AND 
                (processed_at BETWEEN :incoming_start AND :incoming_end)) AS incoming,
            (SELECT
                SUM(`out`) as total
            FROM 
                $statement_items_table
            WHERE 
                deleted_at IS NULL AND 
                invoice_id IS NULL AND 
                (processed_at BETWEEN :outgoing_start AND :outgoing_end)) AS outgoing,
            (SELECT
                SUM(`in`) as total
            FROM 
                $statement_items_table
            WHERE 
                deleted_at IS NULL AND 
                invoice_id IS NULL AND 
                taxable = 1 AND 
                (processed_at BETWEEN :incoming_taxable_start AND :incoming_taxable_end)) AS incoming_taxable,
            (SELECT
                SUM(`out`) as total
            FROM 
                $statement_items_table
            WHERE 
                deleted_at IS NULL AND 
                invoice_id IS NULL AND 
                taxable = 1 AND 
                (processed_at BETWEEN :outgoing_taxable_start AND :outgoing_taxable_end)) AS outgoing_taxable";

        $result =
            (
                new \Phalcon\Mvc\Model\Resultset\Simple(
                    null,
                    $statement_items,
                    $statement_items->getReadConnection()->query(rtrim($query, ','), $params)
                )
            )->toArray();

        $stats['incoming'] += floatval($result[0]['incoming']);
        $stats['outgoing'] += floatval($result[0]['outgoing']);
        $stats['incoming_taxable'] += floatval($result[0]['incoming_taxable']);
        $stats['outgoing_taxable'] += floatval($result[0]['outgoing_taxable']);

        $stats['turnover'] = $stats['incoming'] - $stats['outgoing'];
        $stats['turnover_taxable'] = $stats['incoming_taxable'] - $stats['outgoing_taxable'];

        return $stats;
    }
}
