<?php

/**
 * Settings controller.
 *
 * @package     Kytschi\Phoenix\Controllers\SettingsController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Phoenix\Controllers\ShippingCompaniesController;
use Kytschi\Phoenix\Models\Settings;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

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
