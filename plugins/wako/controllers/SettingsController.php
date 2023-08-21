<?php

/**
 * Settings controller.
 *
 * @package     Kytschi\Wako\Controllers\SettingsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301, USA.
 */

declare(strict_types=1);

namespace Kytschi\Wako\Controllers;

use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Currency;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Wako\Controllers\DividendsController;
use Kytschi\Wako\Models\Settings;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class SettingsController extends ControllerBase
{
    use Currency;
    use Files;
    use Form;
    use Logs;

    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/settings';
    public $resource = 'finance-settings';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->fms . $this->global_url;
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
            'wako/settings/index',
            [
                'currencies' => $this->currencies,
                'data' => $model,
                'users' => (new Users())->find([
                    'conditions' => 'group_id IN("' . implode('","', (new DividendsController())->getGroups()) . '")',
                ])
            ]
        );
    }

    /**
     * Set the model data.
     *
     * @param Kytschi\Phoenix\Models\Settings $model
     * @return Kytschi\Phoenix\Models\Settings $model
     */
    private function setData($model)
    {
        $model->registered_company_name = $_POST['registered_company_name'];
        $model->registered_company_address = $_POST['registered_company_address'];
        $model->registered_company_number = $_POST['registered_company_number'];
        $model->secretary_id = $_POST['secretary_id'];
        $model->shares = !empty($_POST['shares']) ? intval($_POST['shares']) : 1;
        $model->currency = !empty($_POST['currency']) ? $_POST['currency'] : 'GBP';
        $model->paye_tax_ref_number = !empty($_POST['paye_tax_ref_number']) ? $_POST['paye_tax_ref_number'] : '';

        if ($model->shares < 1) {
            $model->shares = 1;
        }
                
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
            if ($model->id === null) {
                if ($model->save() === false) {
                    throw new SaveException(
                        'Failed to update the settings',
                        $model->getMessages()
                    );
                }
            } elseif ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the settings',
                    $model->getMessages()
                );
            }

            if (!empty($_FILES['upload_secretary_signature']['name'])) {
                $this->validFile($_FILES['upload_secretary_signature']);
                $this->clearFiles($this->resource, $model->id);
                
                $this->addFile(
                    $model->id,
                    $_FILES['upload_secretary_signature'],
                    $this->resource,
                    'secretary signature',
                    '',
                    false,
                    true
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
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            'registered_company_name',
            new PresenceOf(
                [
                    'message' => 'The registered company name is required',
                ]
            )
        );

        $validation->add(
            'registered_company_address',
            new PresenceOf(
                [
                    'message' => 'The registered company address is required',
                ]
            )
        );

        $validation->add(
            'registered_company_number',
            new PresenceOf(
                [
                    'message' => 'The registered company number is required',
                ]
            )
        );

        $validation->add(
            'currency',
            new PresenceOf(
                [
                    'message' => 'The currency is required',
                ]
            )
        );

        $validation->add(
            'secretary_id',
            new PresenceOf(
                [
                    'message' => 'The secretary is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Validation failed', $messages);
        }

        if (!$this->validCurrency($_POST['currency'])) {
            throw new ValidationException('Invalid currency');
        }
    }
}
