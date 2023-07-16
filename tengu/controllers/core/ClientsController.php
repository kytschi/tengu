<?php

/**
 * Clients controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\ClientsController
 * @copyright   2023 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Clients;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class ClientsController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Notes;
    use Pagination;
    use Tags;

    public $access = [
        'super-user'
    ];

    public $global_url = '/clients';
    public $resource = 'client';

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Add a client');

        return $this->view->partial(
            'core/clients/add'
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Clients())->findFirst([
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

            $this->saveFormDeleted('Client has been deleted');
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
        $this->setPageTitle('Editing the client');

        $model = (new Clients())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        return $this->view->partial(
            'core/clients/edit',
            [
                'data' => $model,
                'statuses' => $this->defaultStatuses()
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Our clients');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'name',
                'status'
            ],
            'name'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Clients::class)
            ->where('id != ""')
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'name' => '%' . $this->search . '%',
                'search_tags' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('name LIKE :name: OR search_tags LIKE :search_tags:');
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
            'core/clients/index',
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

        $model = (new Clients())->findFirst([
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

            $this->saveFormUpdated("You've recovered the client");
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
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

            $model = $this->setData(new Clients());

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the client',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);

            if (!empty($_FILES['upload_logo']['name'])) {
                $this->addFile(
                    $model->id,
                    $_FILES['upload_logo'],
                    'client-logo',
                    $model->name,
                    '',
                    true
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved();
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/add'));
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

        $model->billing_address_line_1 = !empty($_POST['billing_address_line_1']) ? $_POST['billing_address_line_1'] : null;
        $model->billing_address_line_2 = !empty($_POST['billing_address_line_2']) ? $_POST['billing_address_line_2'] : null;
        $model->billing_city = !empty($_POST['billing_city']) ? $_POST['billing_city'] : null;
        $model->billing_county = !empty($_POST['billing_county']) ? $_POST['billing_county'] : null;
        $model->billing_country = !empty($_POST['billing_country']) ? $_POST['billing_country'] : null;
        $model->billing_postcode = !empty($_POST['billing_postcode']) ? $_POST['billing_postcode'] : null;
        $model->billing_fullname = !empty($_POST['billing_fullname']) ? $_POST['billing_fullname'] : null;
        $model->billing_phone_number = !empty($_POST['billing_phone_number']) ? $_POST['billing_phone_number'] : null;
        $model->billing_email = !empty($_POST['billing_email']) ? $_POST['billing_email'] : null;

        $model->shipping_address_line_1 = !empty($_POST['shipping_address_line_1']) ? $_POST['shipping_address_line_1'] : null;
        $model->shipping_address_line_2 = !empty($_POST['shipping_address_line_2']) ? $_POST['shipping_address_line_2'] : null;
        $model->shipping_city = !empty($_POST['shipping_city']) ? $_POST['shipping_city'] : null;
        $model->shipping_county = !empty($_POST['shipping_county']) ? $_POST['shipping_county'] : null;
        $model->shipping_country = !empty($_POST['shipping_country']) ? $_POST['shipping_country'] : null;
        $model->shipping_postcode = !empty($_POST['shipping_postcode']) ? $_POST['shipping_postcode'] : null;
        $model->shipping_fullname = !empty($_POST['shipping_fullname']) ? $_POST['shipping_fullname'] : null;
        $model->shipping_phone_number = !empty($_POST['shipping_phone_number']) ? $_POST['shipping_phone_number'] : null;
        $model->shipping_email = !empty($_POST['shipping_email']) ? $_POST['shipping_email'] : null;

        $model = $this->addSearchTags($model);

        return $model;
    }

    public function stats()
    {
        $model = new Clients();
        $table = $model->getSource();

        $query = 'SELECT ';
        $query .= "(SELECT COUNT(id) FROM $table WHERE status = 'active') AS active,";
        $query .= "(SELECT COUNT(id) FROM $table WHERE status = 'inactive') AS inactive,";
        $query .= "(SELECT COUNT(id) FROM $table WHERE deleted_at IS NOT NULL) AS deleted,";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Clients())->findFirst([
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
                    'Failed to update the client',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);

            if (!empty($_FILES['upload_logo']['name'])) {
                $this->clearFiles($this->resource, $model->id);

                $this->addFile(
                    $model->id,
                    $_FILES['upload_logo'],
                    'client-logo',
                    $model->name,
                    '',
                    true
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated();
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

    private function validate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            'name',
            new PresenceOf(
                [
                    'message' => 'The name is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
