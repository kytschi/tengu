<?php

/**
 * Settings controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\SettingsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Controllers\Website\ThemesController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Settings;
use Kytschi\Tengu\Models\Website\StatsExclude;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Crypt;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class SettingsController extends ControllerBase
{
    use Form;
    use Logs;
    use Tags;

    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/settings';
    public $resource = 'settings';

    public function initialize()
    {
        $this->global_url = $this->global_url;
    }

    public function indexAction()
    {
        $this->secure($this->access);
        
        $this->clearFormData();

        $this->setPageTitle('Settings');

        $model = (new Settings())->findFirst([
            'name IS NOT NULL'
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        return $this->view->partial(
            'core/settings/index',
            [
                'data' => $model,
                'themes' => (new ThemesController())->tengu_themes
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
        $model->tengu_theme = $_POST['tengu_theme'];

        if (empty($model->cache_key)) {
            $model->cache_key = urlencode((new Crypt())->encrypt(
                file_get_contents(BASE_PATH . '/secure/tengu.pub'),
                "VlRiTGozdGFCcHl0cFZIMUxndjhYcUVEUEE5ZE9TUExvdGhtRTZCSXg0aEdLdFljTW40MTk5UWJWYWtmbEJWK3F4dGUrehgng744kDHDKFHy7t745hdy7Sh34734ksdnlsd0DHHSLDFSYDSNHd"
            ));
        }

        return $model;
    }

    public function updateAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Settings())->findFirst([
            'name IS NOT NULL'
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $this->validate();

            $model = $this->setData($model);

            if ($model->update() === false) {
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
            'tengu_theme',
            new PresenceOf(
                [
                    'message' => 'The theme is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Validation failed', $messages);
        }
    }
}
