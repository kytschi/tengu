<?php

/**
 * My Board controller.
 *
 * @package     Kytschi\Umi\Controllers\MyBoardController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Umi\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\AuthorisationException;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Tengu\Traits\Core\Validation;
use Kytschi\Tengu\Traits\Website\Stats;
use Kytschi\Umi\Models\Boards;
use Kytschi\Umi\Models\BoardColumns;
use Kytschi\Umi\Models\BoardEntries;
use Phalcon\Filter\Validation as PhalconValidation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class MyBoardController extends ControllerBase
{
    use Form;
    use Logs;
    use Notes;
    use Pagination;
    use Stats;
    use Tags;

    public $access = [];

    public $global_url = '';
    public $resource = 'my-board';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->pms . $this->global_url;
    }

    public function createAction()
    {
        $this->secure($this->access);

        try {
            $model = $this->setData(new Boards());

            $model->user_id = self::getUserId();

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the board',
                    $model->getMessages()
                );
            }

            $this->addLog(
                'board',
                $model->id,
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
                    'board_id' => $model->id,
                    'name' => $column,
                    'sort' => $sort
                ]);

                if ($board_column->save() === false) {
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

            $this->clearFormData();
            $this->saveFormSaved('My board successfully created');
            $this->redirect(UrlHelper::backend($this->global_url . '/my-board'));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/my-board/create'));
        } catch (Exception $err) {
            if ($model) {
                $model->delete();
            }
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    public function deleteEntryAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new BoardEntries())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            if ($model->created_by != self::getUserId()) {
                throw new AuthorisationException('You do not have access to this board\'s entry');
            }

            $model->softDelete();

            $this->addLog(
                'board-entry',
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $this->saveFormDeleted('Entry successfully marked as deleted');
            $this->redirect(UrlHelper::backend($this->global_url . '/my-board'));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function indexAction()
    {
        $this->secure();
        $this->setPageTitle('My board');

        return $this->view->partial(
            'umi/my_board/index',
            [
                'board' => (new Boards())->findFirst([
                    'conditions' => 'user_id = :user_id:',
                    'bind' => [
                        'user_id' => self::getUserId()
                    ]
                ]),
                'users' => Users::find(['conditions' => 'deleted_at IS NULL'])
            ]
        );
    }

    private function setData($model)
    {
        $model->name = !empty($_POST['name']) ? $_POST['name'] : 'My board';

        return $model;
    }

    private function validate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new PhalconValidation();

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
