<?php

/**
 * Personas controller.
 *
 * @package     Kytschi\Makabe\Controllers\PersonasController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Makabe\Controllers;

use Kytschi\Makabe\Models\Personas;
use Kytschi\Makabe\Models\PersonaPages;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Controllers\Core\UsersController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Encryption\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class PersonasController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Notes;
    use Pagination;
    use Tags;

    public $access = [
        'administrator',
        'super-user',
        'seo-manager',
        'marketing-manager'
    ];

    public $global_url = '/personas';
    public $resource = 'persona';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->mms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Creating a persona');

        return $this->view->partial(
            'makabe/personas/add'
        );
    }

    public function attachPagePersona($page_id)
    {
        $user_id = self::getUserId();

        $this
            ->modelsManager
            ->executeQuery(
                'UPDATE ' .
                PersonaPages::class .
                ' SET deleted_at=NOW(), deleted_by=:deleted_by: WHERE page_id = :page_id:',
                [
                    'deleted_by' => $user_id,
                    'page_id' => $page_id
                ]
            );

        if (empty($_POST['persona_id'])) {
            return;
        }

        $table = (new PersonaPages())->getSource();

        $this->db->query(
            'INSERT INTO ' . $table . ' 
                (id, persona_id, page_id, created_at, created_by, updated_at, updated_by)
                SELECT
                    :id,
                    :persona_id,
                    :page_id,
                    :created_at,
                    :created_by,
                    :updated_at,
                    :updated_by
                FROM DUAL WHERE NOT EXISTS
                (
                    SELECT id, page_id, persona_id, created_at, created_by, updated_at, updated_by
                    FROM ' . $table . '
                    WHERE page_id=:page_id_2 AND persona_id=:persona_id_2 
                )',
            [
                ':id' => (new Random())->uuid(),
                ':page_id' => $page_id,
                ':page_id_2' => $page_id,
                ':persona_id' => $_POST['persona_id'],
                ':persona_id_2' => $_POST['persona_id'],
                ':created_at' => date('Y-m-d H:i:s'),
                ':created_by' => $user_id,
                ':updated_at' => date('Y-m-d H:i:s'),
                ':updated_by' => $user_id
            ]
        );

        $this->db->query(
            'UPDATE ' . $table . '
            SET deleted_at=NULL, deleted_by=NULL 
            WHERE page_id=:page_id AND persona_id=:persona_id;',
            [
                ':page_id' => $page_id,
                ':persona_id' => $_POST['persona_id']
            ]
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Personas())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->softDelete(true);

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

            $this->saveFormDeleted('Persona has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
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

        $model = (new Personas())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle('Editing the persona');

        return $this->view->partial(
            'makabe/personas/edit',
            [
                'data' => $model
            ]
        );
    }

    public function get()
    {
        return (new Personas())->find(
            [
                'conditions' => 'deleted_at IS NULL',
                'order' => 'first_name'
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Our personas');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'first_name',
                'last_name',
                'created_by'
            ],
            'first_name'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Personas::class)
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'first_name' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('first_name LIKE :first_name:');
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
            'makabe/personas/index',
            [
                'data' => $paginator->paginate(),
                'stats' => $this->stats()
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Personas())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->recover(true);

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

            $this->saveFormUpdated('Persona has been recovered');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function saveAction()
    {
        $this->secure($this->access);

        try {
            $this->validate();

            $model = $this->setData(new Personas());

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to add the persona',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);

            if (!empty($_FILES['upload_picture']['tmp_name'])) {
                (new UsersController())->addProfileImage($model);
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved('Persona has been saved');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/build'));
        } catch (Exception $err) {
            if (!empty($model)) {
                $model->delete();
            }
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function setData($model)
    {
        $model->first_name = $_POST['first_name'];
        $model->last_name = $_POST['last_name'];
        $model->title = !empty($_POST['title']) ? $_POST['title'] : null;
        $model->job_title = !empty($_POST['job_title']) ? $_POST['job_title'] : null;
        $model->email = !empty($_POST['email']) ? $_POST['email'] : null;
        $model->phone_number = !empty($_POST['phone_number']) ? $_POST['phone_number'] : null;
        $model->mobile_number = !empty($_POST['mobile_number']) ? $_POST['mobile_number'] : null;
        $model->default = isset($_POST['default']) ? 1 : 0;

        return $model;
    }

    public function stats()
    {
        $table = (new Personas())->getSource();

        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE status = 'active') AS active,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'inactive') AS inactive,";
        $query .= "(SELECT count(id) FROM $table WHERE deleted_at IS NOT NULL) AS deleted,";

        $model = new Personas();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Personas())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $this->validate();

            $model = $this->setData($model);

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the persona',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);

            if (!empty($_FILES['upload_picture']['tmp_name'])) {
                (new UsersController())->addProfileImage($model);
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Persona has been updated');

            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function validate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            'first_name',
            new PresenceOf(
                [
                    'message' => 'The first name is required',
                ]
            )
        );

        $validation->add(
            'last_name',
            new PresenceOf(
                [
                    'message' => 'The last name is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
