<?php

/**
 * Dashboards controller.
 *
 * @package     Kytschi\Izumi\Controllers\DashboardsController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Izumi\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;

class DashboardsController extends ControllerBase
{
    public function indexAction()
    {
        $this->secure();
        $this->setPageTitle('Dashboard');

        return $this->view->partial(
            'dashboards/sales_index'
        );
    }
}
