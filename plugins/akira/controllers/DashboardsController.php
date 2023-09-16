<?php

/**
 * Dashboards controller.
 *
 * @package     Kytschi\Akira\Controllers\DashboardsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Akira\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;

class DashboardsController extends ControllerBase
{
    public function indexAction()
    {
        $this->secure();
        $this->setPageTitle('Dashboard');

        return $this->view->partial(
            'akira/dashboards/index'
        );
    }
}
