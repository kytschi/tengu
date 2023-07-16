<?php

/**
 * Templates controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\TemplatesController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Website;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Templates;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Queue;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class TemplatesController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Pagination;
    use Queue;

    public $access = [
        'super-user'
    ];

    public $global_url = '/templates';
    public $resource = 'template';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Build a template');

        return $this->view->partial(
            'website/templates/add',
            [
                'statuses' => $this->defaultStatuses()
            ]
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Templates())->findFirst([
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

            $this->saveFormDeleted('Template successfully marked as deleted');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    public function editAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Editing the template');

        $model = (new Templates())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        return $this->view->partial(
            'website/templates/edit',
            [
                'data' => $model,
                'statuses' => $this->defaultStatuses()
            ]
        );
    }

    public function filesAction()
    {
        $this->saveFormData($_POST);

        if (empty($_POST['name'])) {
            throw new RequestException('Missing required data');
        }

        if (!is_array($_POST['name'])) {
            throw new RequestException('Invalid data');
        }

        if (!empty($_POST['file_new'])) {
            if (empty($_POST['file_new'])) {
                $this->saveFormError('Form validation failed, please double check the required fields');
                throw new ValidationException(
                    'Form validation failed, please double check the required fields',
                    null,
                    400,
                    UrlHelper::backend($this->global_url)
                );
            }

            if (empty($_POST['default_new'])) {
                $this
                    ->modelsManager
                    ->executeQuery('UPDATE ' . Templates::class . ' SET default=0');
            }

            $splits = explode('.', $_POST['file_new']);
            if (count($splits) > 1) {
                unset($splits[count($splits) - 1]);
            }

            $file = trim($_POST['folder_new'] . '/' . implode($splits), '/');

            $model = new Templates([
                'file' => $file,
                'name' => !empty($_POST['name_new']) ? $_POST['name_new'] : str_replace(['/', '-', '_'], ' ', $file),
                'slug' => $this->createSlug($file),
                'default' => !empty($_POST['default_new']) ? true : false
            ]);

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the template',
                    $model->getMessages()
                );
            }
        }

        try {
            foreach ($_POST['name'] as $id => $name) {
                $model = (new Templates())->findFirst([
                    'conditions' => 'id = :id:',
                    'bind' => [
                        'id' => $id
                    ]
                ]);

                if (empty($model)) {
                    return $this->notFound('Template not found');
                }

                $model->name = $name;
                if (!empty($_POST['default'])) {
                    $model->default = !empty($_POST['default'][$id]) ? true : false;
                }

                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the template',
                        $model->getMessages()
                    );
                }

                $this->addJobToQueue($this->resource, $model->id, 'Kytschi\Tengu\Jobs\TemplateSnapshot');
            }

            $this->saveFormSaved('Templates successfully updated');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException($err->getMessage());
        }
    }

    private function processFolders(&$folders)
    {
        $viewsDir = ($this->di->getConfig())->application->siteViewsDir;
        foreach ($folders as $key => $folder) {
            if ($sub_folders = glob($folder . '/*', GLOB_ONLYDIR)) {
                $this->processFolders($sub_folders);
                $folders = array_merge($folders, $sub_folders);
            }
            $folders[$key] = str_replace($viewsDir . '/', '', $folder);
        }
    }

    public function indexAction()
    {
        //$this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Templates');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'created_by',
                'name',
                'slug',
                'status'
            ],
            'name'
        );

        $opts = [];

        if (!empty($this->search)) {
            $opts = [
               'conditions' => 'name LIKE :name: OR slug LIKE :slug: OR file LIKE :file:',
               'bind' => [
                    'name' => '%' . $this->search . '%',
                    'slug' => '%' . $this->search . '%',
                    'file' => '%' . $this->search . '%'
               ]
            ];
        }

        $viewsDir = ($this->di->getConfig())->application->siteViewsDir;
        $folders = glob($viewsDir . '/*', GLOB_ONLYDIR);
        $this->processFolders($folders);

        return $this->view->partial(
            'website/templates/index',
            [
                'data' => (new Templates())->find($opts),
                'folders' => $folders
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Templates())->findFirst([
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

            $this->saveFormUpdated('Template successfully recovered');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    public function saveAction()
    {
        $this->secure($this->access);

        try {
            $this->validate(true);

            $model = $this->setData(new Templates());

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the template',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->clearFormData();
            $this->saveFormSaved('Template successfully created');
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/add'));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage()
            );
        }
    }

    private function setData($model)
    {
        $model->file = str_replace(['.phtml', '.volt'], '', $_POST['file']);
        $model->name = !empty($_POST['name']) ? $_POST['name'] : $model->file;
        $model->slug = empty($_POST['slug']) ? $this->createSlug($model->name) : $_POST['slug'];
        $model->default = isset($_POST['default']) ? true : false;

        return $model;
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Templates())->findFirst([
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
                    'Failed to update the template',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->addJobToQueue($this->resource, $model->id, 'Kytschi\Tengu\Jobs\TemplateSnapshot', 'low');

            $this->clearFormData();
            $this->saveFormUpdated('Template successfully updated');
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
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

        if (!empty($_POST['file_new'])) {
            $add = true;
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        if ($add) {
            $validation->add(
                'file_new',
                new PresenceOf(
                    [
                        'message' => 'The name is required',
                    ]
                )
            );
        } else {
            $validation->add(
                'file',
                new PresenceOf(
                    [
                        'message' => 'The file is required',
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
