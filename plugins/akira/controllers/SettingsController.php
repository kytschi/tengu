<?php

/**
 * Settings controller.
 *
 * @package     Kytschi\Akira\Controllers\SettingsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Akira\Controllers;

use Kytschi\Akira\Models\Settings;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class SettingsController extends ControllerBase
{
    use Form;
    use Logs;

    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/appointments/settings';
    public $resource = 'settings';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->css . $this->global_url;
    }

    public function indexAction()
    {
        $this->secure($this->access);
        $this->clearFormData();
        $this->setPageTitle('Settings');

        $model = (new Settings())->findFirst([
            'id IS NOT NULL'
        ]);
        if (empty($model)) {
            $model = new Settings();
        }

        return $this->view->partial(
            'akira/appointments/settings/index',
            [
                'data' => $model,
                'url' => $this->global_url,
                'back_url' => ($this->di->getConfig())->urls->css . '/appointments'
            ]
        );
    }

    /**
     * Set the model data.
     *
     * @param Kytschi\Tengu\Models\Settings $model
     * @return Kytschi\Tengu\Models\Settings $model
     */
    private function setData($model)
    {
        $model->webdav_url = !empty($_POST['webdav_url']) ? $_POST['webdav_url'] : '';
        $model->webdav_auth = !empty($_POST['webdav_user']) ? $_POST['webdav_user'] : '';
        $model->webdav_auth_two = !empty($_POST['webdav_password']) ?
            ($_POST['webdav_password'] != 'ATENGUPASSWORDISSET' ? $_POST['webdav_password'] : '')
            : '';

        return $model;
    }

    public function updateAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Settings())->findFirst([
            'id IS NOT NULL'
        ]);
        if (empty($model)) {
            $model = new Settings();
        }

        try {
            $this->validate();

            $model = $this->setData($model);

            if ($model->id) {
                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the settings',
                        $model->getMessages()
                    );
                }
            } elseif ($model->save() === false) {
                throw new SaveException(
                    'Failed to save the settings',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Settings have been updated');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    private function validate($data = null)
    {
        return;
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
            throw new ValidationException('Validation failed', $messages);
        }
    }
}
