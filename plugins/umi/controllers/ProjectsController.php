<?php

/**
 * Projects controller.
 *
 * @package     Kytschi\Umi\Controllers\ProjectsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Umi\Controllers;

use Kytschi\Tengu\Controllers\Core\GroupsController;
use Kytschi\Tengu\Controllers\Core\ProjectsController as ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Umi\Models\Boards;
use Kytschi\Umi\Models\BoardColumns;
use Kytschi\Umi\Models\Projects;
use Kytschi\Wako\Models\StatementItems;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class ProjectsController extends ControllerBase
{
    public $access = [
        'administrator',
        'super-user',
        'project-manager'
    ];

    public $global_url = '/projects';
    public $resource = 'project';
    public $dir = 'umi/projects';

    public $include_groups = [
        'Super user',
        'Administrator',
    ];

    private $include_group_ids = [];

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->pms . $this->global_url;
        $this->getGroups();
    }

    public function editAction()
    {
        $this->secure($this->access);

        $model = (new Projects())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle($model->name);

        return $this->view->partial(
            $this->dir . '/edit',
            [
                'data' => $model,
                'stats' => $this->editStats(),
                'users' => (new Users())->find([
                    'conditions' => 'group_id IN("' . implode('","', $this->include_group_ids) . '")',
                ])
            ]
        );
    }

    public function getGroups()
    {
        $controller = new GroupsController();
        foreach ($this->include_groups as $group) {
            if ($id = $controller->getGroup($group)) {
                $this->include_group_ids[] =  $id[0];
            }
        }
        return $this->include_group_ids;
    }

    public function saveAction($redirect = true)
    {
        $model = parent::saveAction(false);

        $board = $this->setData(new Boards());
        $board->project_id = $model->id;

        if ($board->save() === false) {
            $model->delete();
            throw new SaveException(
                'Failed to create the board',
                $model->getMessages()
            );
        }

        $this->addLog(
            'board',
            $board->id,
            'info',
            'Created by ' . $this->getUserFullName()
        );

        $columns = [
            'To do',
            'Blocked',
            'In progress',
            'Testing',
            'Done'
        ];

        foreach ($columns as $sort => $column) {
            $board_column = new BoardColumns([
                'board_id' => $board->id,
                'name' => $column,
                'sort' => $sort
            ]);

            if ($board_column->save() === false) {
                $board->delete();
                $model->delete();
                throw new SaveException(
                    'Failed to create the board column',
                    $board_column->getMessages()
                );
            }

            $this->addLog(
                'board-column',
                $board_column->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );
        }

        $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
    }

    public function editStats()
    {
        $stats = [
            'incoming' => 0.00
        ];

        $model = new StatementItems();
        $table = $model->getSource();

        $params = [
            'project_id' => $this->dispatcher->getParam('id')
        ];

        $query = "SELECT
            (
                SELECT
                    SUM(`in`)
                FROM 
                    $table
                WHERE 
                    deleted_at IS NULL AND 
                    project_id = :project_id
            ) AS incoming,
            (
                SELECT
                    SUM(`out`)
                FROM 
                    $table
                WHERE 
                    deleted_at IS NULL AND 
                    project_id = :project_id
            ) AS outgoing";

        $result = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','), $params)
        ))->toArray()[0];

        if ($result) {
            $stats['incoming'] = $result['incoming'];
            $stats['outgoing'] = $result['outgoing'];
        }

        return $stats;
    }
}
