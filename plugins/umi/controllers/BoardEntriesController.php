<?php

/**
 * Boards controller.
 *
 * @package     Kytschi\Umi\Controllers\BoardEntriesController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Umi\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\AuthorisationException;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Validation;
use Kytschi\Umi\Models\BoardColumns;
use Kytschi\Umi\Models\BoardEntries;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Validation as PhalconValidation;
use Phalcon\Validation\Validator\PresenceOf;

class BoardEntriesController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Notes;
    use Validation;

    public $access = [
        'administrator',
        'super-user',
        'project-manager'
    ];

    public $global_url = '/boards/entries';
    public $resource = 'board-entry';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->pms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);

        $url = $this->global_url . '/my-board';
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        try {
            $column = (new BoardColumns())->findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $this->dispatcher->getParam('id')
                ]
            ]);

            if (empty($column)) {
                throw new Exception('Board column not found');
            }

            if (!empty($column->board->user_id)) {
                if ($column->board->user_id != self::getUserId()) {
                    throw new AuthorisationException('You do not have access to this board\'s column');
                }
            }

            $this->validate();

            $model = $this->setData(new BoardEntries());
            $model->board_column_id = $this->dispatcher->getParam('id');

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the board entry',
                    $model->getMessages()
                );
            }

            $this->addLog(
                'board-entry',
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->clearFormData();
            $this->saveFormSaved('Board entry successfully created');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    public function deleteAction()
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
            $model->softDelete();

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Board entry has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function getDue()
    {
        return BoardEntries::find(
            [
                'conditions' => 'due_on <= NOW() AND deleted_at IS NULL AND status != "complete"'
            ]
        );
    }

    public function recoverAction()
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
            $model->recover();

            $this->addLog(
                $this->resource,
                $model->id,
                'warning',
                'Recovered by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Board entry has been recovered');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    private function setData($model)
    {
        $model->title = $_POST['title'];
        $model->description = !empty($_POST['description']) ? $_POST['description'] : '';
        $model->sort = array_key_exists('sort', $_POST) ? intval($_POST['sort']) : 2000;
        $model->assign_to = !empty($_POST['assign_to']) ? $_POST['assign_to'] : self::getUserId();
        $model->due_on = !empty($_POST['due_on']) ? DateHelper::sql($_POST['due_on']) : null;

        return $model;
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $url = $this->global_url . '/my-board';
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        try {
            $this->validate(true);

            $model = (new BoardEntries())->findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $_POST['entry_id']
                ]
            ]);

            if (empty($model)) {
                return $this->notFound();
            }

            if ($model->created_by != self::getUserId()) {
                throw new AuthorisationException('You do not have access to this board\'s entry');
            }

            $model = $this->setData($model);

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the board entry',
                    $model->getMessages()
                );
            }

            $this->addFiles($this->resource, $model->id);
            $this->addNoteFromRequest($model->id);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Board entry successfully updated');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($url));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($url));
        } catch (Exception $err) {
            if ($model) {
                $model->delete();
            }
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    private function validate($update = false)
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new PhalconValidation();

        $validation->add(
            'title',
            new PresenceOf(
                [
                    'message' => 'The title is required',
                ]
            )
        );

        if ($update) {
            $validation->add(
                'entry_id',
                new PresenceOf(
                    [
                        'message' => 'The entry id is required',
                    ]
                )
            );
        }

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
