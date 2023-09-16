<?php

/**
 * Spun export job controller.
 *
 * @package     Kytschi\Makabe\Jobs\SpunExportJob
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Makabe\Jobs;

use Kytschi\Makabe\Exceptions\SpinException;
use Kytschi\Makabe\Models\SpinContent;
use Kytschi\Makabe\Models\SpunContent;
use Kytschi\Makabe\Models\Exports;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notifications;
use Kytschi\Tengu\Traits\Core\User;
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model;
use Phalcon\Paginator\Adapter\QueryBuilder;

class SpunExportjob extends Controller
{
    use Logs;
    use Notifications;
    use User;
    
    private $job;

    public $resource = 'spun-export-job';

    public function run($job)
    {
        $this->job = $job;
                
        $paginator = new Model(
            [
                'model' => SpunContent::class,
                'parameters' => [
                    'deleted_at IS NULL AND spin_content_id=:id:',
                    'bind' => [
                        'id' => $this->job->resource_id
                    ]
                ],
                'limit' => 100,
                'page' => 1
            ]
        );

        $paginate = $paginator->paginate();
        if (!$paginate->total_items) {
            $this->notify(
                $job->created_by,
                $job->resource,
                $job->resource_id,
                'info',
                'Spun content export successfully complete and ready for download'
            );
            return;
        }

        $pages = $paginate->getLast();

        $key = time();

        mkdir(($this->di->getConfig())->application->dumpDir . $key);

        for ($page = 2; $page <= $pages; $page++) {
            $results = $paginate->getItems();
            if (empty($results)) {
                return;
            }

            foreach ($results as $result) {
                file_put_contents(
                    ($this->di->getConfig())->application->dumpDir . $key . '/' . $result['id'] . '.html',
                    $result['content']
                );
            }

            $paginator = new Model(
                [
                    'model' => SpunContent::class,
                    'parameters' => [
                        'deleted_at IS NULL AND spin_content_id=:id:',
                        'bind' => [
                            'id' => $this->job->resource_id
                        ]
                    ],
                    'limit' => 100,
                    'page' => $page
                ]
            );
            $paginate = $paginator->paginate();
        }

        shell_exec(
            'zip -r -j ' .
            ($this->di->getConfig())->application->dumpDir . $key . '.zip ' .
            ($this->di->getConfig())->application->dumpDir . $key . '/*'
        );

        $model = new Exports([
            'name' => $key . '.zip',
            'resource_id' => $this->job->resource_id,
            'resource' => 'spun-export'
        ]);

        if ($model->save() === false) {
            throw new SaveException(
                'Failed to save the export',
                $model->getMessages()
            );
        }

        shell_exec('rm -R ' . ($this->di->getConfig())->application->dumpDir . $key);

        $this->notify(
            $job->created_by,
            $job->resource,
            $job->resource_id,
            'info',
            'Spun content export successfully complete and ready for download'
        );
    }
}
