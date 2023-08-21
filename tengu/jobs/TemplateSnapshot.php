<?php

/**
 * Template snapshot job controller.
 *
 * @package     Kytschi\Tengu\Jobs\TemplateSnapshot
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Jobs;

use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notifications;
use Kytschi\Tengu\Traits\Core\User;
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder;

class TemplateSnapshot extends Controller
{
    use Logs;
    use Notifications;
    use User;

    public $resource = 'template-snapshot';

    public function run($job)
    {
        try {
            $model = (new Pages())->findFirst([
                'conditions' => 'template_id = :template_id:',
                'bind' => [
                    'template_id' => $job->resource_id
                ]
            ]);

            if (empty($model)) {
                return;
            }

            $file = ($this->di->getConfig())->application->dumpDir . $model->template_id . '-snap.jpg';

            $scan_width = 1600;
            $scan_height = 1400;

            $save_width = 1024;
            $save_height = 768;

            $url = ($this->di->getConfig())->application->appUrl .
                $model->url .
                '?tengu-stat-ignore=true';

            $output = shell_exec(
                'xvfb-run --server-args="-screen 0, ' . $scan_width . 'x' . $scan_height . 'x24" ' .
                '/usr/bin/cutycapt --url=' . $url .
                ' --out=' . $file . ' --min-width=' . $save_width . ' --min-height=' . $save_height
            );
        } catch (\Exception $err) {
            $this->notify(
                $job->created_by,
                $job->resource,
                $job->resource_id,
                'error',
                'Template snapshot has failed'
            );

            throw $err;
        }
    }
}
