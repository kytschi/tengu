<?php

/**
 * Settings controller.
 *
 * @package     Kytschi\Phoenix\Controllers\SettingsController
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

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Phoenix\Controllers\ShippingCompaniesController;
use Kytschi\Phoenix\Models\PaymentGateway;
use Kytschi\Phoenix\Models\Settings;
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

    public $global_url  = '/settings';
    public $resource = 'sales-settings';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->sales . $this->global_url;
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
            'phoenix/settings/index',
            [
                'data' => $model,
                'shipping_companies' => (new ShippingCompaniesController())->get(),
                'payment_gateways' => PaymentGateway::find(['conditions' => 'deleted_at IS NULL']),
                'url' => $this->global_url
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
        $model->vat = !empty($_POST['vat']) ? floatval($_POST['vat']) : 0.00;
        $model->zero_stock = !empty($_POST['zero_stock']) ? intval($_POST['zero_stock']) : 0;
        $model->onscreen_keyboard = !empty($_POST['onscreen_keyboard']) ? intval($_POST['onscreen_keyboard']) : 0;
        $model->default_shipping = !empty($_POST['default_shipping']) ? $_POST['default_shipping'] : '';
        $model->default_payment_gateway = !empty($_POST['default_payment_gateway']) ? $_POST['default_payment_gateway'] : '';

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

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this
                ->modelsManager
                ->executeQuery('UPDATE ' . PaymentGateway::class . ' SET status="inactive", default=0');

            if (!empty($_POST['payment_gateways'])) {
                foreach ($_POST['payment_gateways'] as $id => $value) {
                    $default = 0;
                    if ($id == $model->default_payment_gateway) {
                        $default = 1;
                    }
                    $payment = PaymentGateway::findFirst([
                        'conditions' => 'id=:id:',
                        'bind' => [
                            'id' => $id
                        ]
                    ]);
                    if (empty($payment)) {
                        continue;
                    }

                    $payment->status = 'active';
                    $payment->default = $default;
                    if ($payment->update() === false) {
                        throw new SaveException(
                            'Failed to update the payment gateway',
                            $payment->getMessages()
                        );
                    }
                }
            }

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
            'vat',
            new PresenceOf(
                [
                    'message' => 'The VAT is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Validation failed', $messages);
        }
    }
}
