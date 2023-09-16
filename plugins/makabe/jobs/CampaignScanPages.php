<?php

/**
 * Campaign scan pages job controller.
 *
 * @package     Kytschi\Makabe\Jobs\CampaignScanPages
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
 *
 * Copyright 2023 Mike Welsh

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
