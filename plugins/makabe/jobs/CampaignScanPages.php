<?php

/**
 * Campaign scan pages job controller.
 *
 * @package     Kytschi\Makabe\Jobs\CampaignScanPages
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
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

namespace Kytschi\Makabe\Jobs;

use Kytschi\Makabe\Controllers\PageScannerController;
use Kytschi\Makabe\Models\Campaigns;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notifications;
use Kytschi\Tengu\Traits\Core\User;
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder;

class CampaignScanPages extends Controller
{
    use Logs;
    use Notifications;
    use User;

    private $max_spins = 10000;
    private $job;
    private $keywords = [];

    public $resource = 'campaign-scan-pages';

    public function run($job)
    {
        try {
            $model = (new Campaigns())->findFirst([
                'conditions' => 'deleted_at IS NULL AND id=:id:',
                'bind' => [
                    'id' => $job->resource_id
                ]
            ]);
            if (empty($model)) {
                return;
            }

            $controller = new PageScannerController();

            foreach ($model->scan_pages as $page) {
                if (empty($page->last_scanned)) {
                    $controller->scan($page);
                }
            }

            $this->notify(
                $job->created_by,
                $job->resource,
                $job->resource_id,
                'info',
                'Campaign pages have been successfully scanned'
            );
        } catch (\Exception $err) {
            $this->notify(
                $job->created_by,
                $job->resource,
                $job->resource_id,
                'danger',
                'One or more campaign pages have failed to scan'
            );

            $this->addLog(
                $job->resource,
                $job->resource_id,
                'danger',
                'One or more campaign pages have failed to scan',
                $err->getMessage()
            );

            throw $err;
        }
    }
}
