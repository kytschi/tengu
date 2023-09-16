<?php

/**
 * Notifications controller.
 *
 * @package     Kytschi\Umi\Controllers\NotificationsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
