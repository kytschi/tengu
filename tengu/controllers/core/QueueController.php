<?php

/**
 * Queue controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\QueueController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Amp\Parallel\Context\Thread;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Queue;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Pagination;
use Phalcon\Paginator\Adapter\QueryBuilder;

class QueueController extends ControllerBase
{
    use Form;
    use Logs;
    use Pagination;

    private $max_running = 4;

    public $access = [
        'super-user'
    ];

    public $global_url = '/queue';
    public $resource = 'queue';
    private $job_id = null;

    public function clearAction($status)
    {
        $this->clearFormData();

        $this->secure($this->access);

        try {
            $this
                ->modelsManager
                ->executeQuery(
                    'DELETE FROM ' . Queue::class . ' WHERE status = :status:',
                    ['status' => $status]
                );

            $this->saveFormDeleted('Queue have been cleared');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Queue())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->softDelete();

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $this->saveFormDeleted('Queue job has been deleted');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function editAction()
    {
        $this->secure($this->access);

        $this->setPageTitle('Viewing the queue job');

        $model = (new Queue())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        return $this->view->partial(
            'core/queue/edit',
            [
                'data' => $model
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Jobs queue');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'resource',
                'job',
                'status'
            ],
            'resource'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Queue::class)
            ->where('deleted_at IS NULL')
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'resource' => '%' . $this->search . '%',
                'job' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('resource LIKE :resource: OR job LIKE :job:');
        }

        if (!empty($this->filters)) {
            $iLoop = 1;
            foreach ($this->filters as $filter => $value) {
                if (empty($value)) {
                    continue;
                }
                switch ($filter) {
                    case 'status':
                        $builder->andWhere('status LIKE :status_' . $iLoop . ':');
                        $params['status_' . $iLoop] = $value;
                        $iLoop++;
                        break;
                }
            }
        }

        $builder->setBindParams($params);

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        return $this->view->partial(
            'core/queue/index',
            [
                'data' => $paginator->paginate(),
                'stats' => $this->stats()
            ]
        );
    }

    private function purge()
    {
        $date = date('Y-m-d 00:00:00', strtotime("-7 days"));

        $this
            ->modelsManager
            ->executeQuery('DELETE FROM ' . Queue::class .
            ' WHERE status IN ("complete", "deleted_at") 
            AND (completed_at <= "' . $date . '" OR deleted_at <= "' . $date . '")');
    }

    public function stats()
    {
        $table = (new Queue())->getSource();

        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE status = 'running') AS running,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'pending') AS pending,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'complete') AS complete,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'failed') AS failed";

        $model = new Queue();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function trigger()
    {
        $jobs = [];
        try {
            echo "Queue triggered\n";

            $this->purge();

            $running = (new Queue())->find([
                'conditions' => 'status = "running" AND deleted_at IS NULL'
            ]);
            if (!empty($running->count())) {
                if ($running->count() >= $this->max_running) {
                    return;
                }
            }

            $results = (new Queue())->find([
                'conditions' => 'status = "pending" AND deleted_at IS NULL',
                'order' => 'priority DESC',
                'limit' => $this->max_running - $running->count()
            ]);
            if (empty($results->count())) {
                return;
            }

            $running = null;

            foreach ($results as $key => $result) {
                $class = $result->job;
                if (!class_exists($class)) {
                    throw new \Exception('Job class, "' . $class . '", not found');
                }

                $job = (new $class());

                $result->status = 'running';
                $result->started_at = date('Y-m-d H:i:s');
                if ($result->update() === false) {
                    $this->addLog(
                        $job->resource,
                        $resspin_content_idult->id,
                        'danger',
                        'Failed to update the job'
                    );

                    throw new SaveException(
                        'Failed to update the job',
                        $result->getMessages()
                    );
                }

                $jobs[$key] = [
                    $job,
                    $result
                ];

                $this->addLog(
                    $job->resource,
                    $result->id,
                    'info',
                    'Job is running'
                );
            }

            foreach ($jobs as $key => $job) {
                $this->resource = $job[0]->resource;
                $this->job_id = $job[1]->id;

                $running = $job[1];

                $job[0]->run($job[1]);

                $job[1]->status = 'complete';
                $job[1]->completed_at = date('Y-m-d H:i:s');
                if ($job[1]->update() === false) {
                    $this->addLog(
                        $job[0]->resource,
                        $job[1]->id,
                        'danger',
                        'Failed to update the job'
                    );

                    throw new SaveException(
                        'Failed to update the job',
                        $this->job->getMessages()
                    );
                }

                $this->addLog(
                    $job[0]->resource,
                    $job[1]->id,
                    'success',
                    'Job is complete'
                );
            }
            echo "Complete\n";
        } catch (\Exception $err) {
            $this->addLog(
                $this->resource,
                $this->job_id,
                'error',
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );

            $running->status = 'failed';
            $running->update();

            echo $err->getMessage() . "\n";
            //die();
        }
    }
}
