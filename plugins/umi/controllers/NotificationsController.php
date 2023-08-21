<?php

/**
 * Notifications controller.
 *
 * @package     Kytschi\Umi\Controllers\NotificationsController
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

namespace Kytschi\Umi\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notifications;
use Kytschi\Umi\Controllers\BoardEntriesController;

class NotificationsController extends ControllerBase
{
    use Logs;
    use Notifications;

    public $resource = 'notifications';

    public function trigger()
    {
        try {
            $controller = new BoardEntriesController();
            $due = $controller->getDue();

            if (empty($due)) {
                return;
            }

            foreach ($due as $entry) {
                $this->notify(
                    $entry->assign_to,
                    $controller->resource,
                    $entry->id,
                    'info',
                    'Board task is due',
                    'Board task, ' . $entry->title . ', is due',
                    ($this->di->getConfig())->urls->pms . '/my-board?entry=form-umi-edit-entry-' . $entry->id,
                    true
                );
            }
        } catch (\Exception $err) {
            $this->addLog(
                $this->resource,
                null,
                'danger',
                $err->getMessage()
            );
        }
    }
}
