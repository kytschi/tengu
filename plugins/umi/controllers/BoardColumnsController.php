<?php

/**
 * Boards controller.
 *
 * @package     Kytschi\Umi\Controllers\BoardColumnsController
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
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Validation;
use Kytschi\Umi\Models\BoardColumns;
use Kytschi\Umi\Models\BoardEntries;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation as PhalconValidation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class BoardColumnsController extends ControllerBase
{
    use Form;
    use Logs;
    use Validation;

    public $access = [
        'administrator',
        'super-user',
        'project-manager'
    ];

    public $global_url = '';
    public $resource = 'board-column';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->pms . $this->global_url;
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $url = $this->global_url . '/my-board';
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        $model = (new BoardColumns())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            if ($model->entries->count()) {
                throw new ValidationException(
                    'You can not delete the column as it has entries. Move them to another column before deleting'
                );
            }

            $model->softDelete();

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/my-board';
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Board column has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $url = $this->global_url . '/my-board';
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        $model = (new BoardColumns())->findFirst([
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

            $url = $this->global_url . '/my-board';
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Board entry has been recovered');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    private function setData($model)
    {
        $model->name = $_POST['name'];
        $model->sort = $_POST['sort'];
        $model->entry_status = $_POST['entry_status'];

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

            $model = (new BoardColumns())->findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $_POST['column_id']
                ]
            ]);

            if (empty($model)) {
                return $this->notFound();
            }

            if ($model->created_by != self::getUserId()) {
                throw new AuthorisationException('You do not have access to this board\'s column');
            }

            $old_sort = $model->sort;
            $sorts = [];
            foreach ($model->board->columns as $column) {
                $sorts[] = $column->id;
            }

            $model = $this->setData($model);

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the board column',
                    $model->getMessages()
                );
            }

            $sorts[$old_sort] = $sorts[$model->sort];
            $sorts[$model->sort] = $model->id;

            $table = (new BoardColumns())->getSource();
            $query = '';
            $params = [];

            foreach ($sorts as $sort => $id) {
                $params[':sort_' . $sort] = $sort;
                $params[':id_' . $sort] = $id;

                $query .= 'UPDATE ' . $table . ' SET sort = :sort_' . $sort . ' WHERE id = :id_' . $sort . ';';
            }

            $this
                ->db
                ->query(
                    $query,
                    $params
                );

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Board column successfully updated');
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

    public function updateEntriesAction()
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

            $this->validateEntries();

            $table = (new BoardEntries())->getSource();

            $query = '';
            $params = [];

            foreach ($_POST['entry_id'] as $key => $id) {
                if (!array_key_exists($key, $_POST['sort'])) {
                    throw new Exception('Invalid sort');
                }

                $params[':sort_' . $key] = $_POST['sort'][$key];
                $params[':id_' . $key] = $id;
                $params[':status_' . $key] = $column->entry_status;

                $query .= 'UPDATE ' . $table .
                    ' SET
                        sort = :sort_' . $key . ',
                        board_column_id = "' . $column->id . '",
                        status=:status_' . $key .
                    ' WHERE id = :id_' . $key . ';';
            }

            $this
                ->db
                ->query(
                    $query,
                    $params
                );

            $this->addLog(
                'board-column',
                $column->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->clearFormData();
            $this->saveFormSaved('Board successfully updated');
            $this->redirect(UrlHelper::backend($url));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    private function validate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new PhalconValidation();

        $validation->add(
            'name',
            new PresenceOf(
                [
                    'message' => 'The title is required',
                ]
            )
        );

        $validation->add(
            'sort',
            new PresenceOf(
                [
                    'message' => 'The sort is required',
                ]
            )
        );

        $validation->add(
            'column_id',
            new PresenceOf(
                [
                    'message' => 'The column ID is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }

    private function validateEntries()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new PhalconValidation();

        $validation->add(
            'entry_id',
            new PresenceOf(
                [
                    'message' => 'The entry ID is required',
                ]
            )
        );

        $validation->add(
            'sort',
            new PresenceOf(
                [
                    'message' => 'The sort is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
