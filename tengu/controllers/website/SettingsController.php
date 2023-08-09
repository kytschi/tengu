<?php

/**
 * Settings controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\SettingsController
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

namespace Kytschi\Tengu\Controllers\Website;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Settings;
use Kytschi\Tengu\Models\Website\StatsExclude;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Encryption\Crypt;
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
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    private function addStatsExclude()
    {
        if (empty($_POST['stats_exclude'])) {
            return;
        }

        if (
            $model = (new StatsExclude())->findFirst(
                [
                    'conditions' => 'exclude = :exclude:',
                    'bind' => [
                        'exclude' => $_POST['stats_exclude']
                    ]
                ]
            )
        ) {
            $model->deleted_at = null;
            $model->deleted_by = null;

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to add the exclude',
                    $model->getMessages()
                );
            }

            $this->saveFormUpdated('Exclude has been saved');
            return;
        }

        $model = new StatsExclude([
            'exclude' => $_POST['stats_exclude']
        ]);

        if ($model->save() === false) {
            throw new SaveException(
                'Failed to add the exclude',
                $model->getMessages()
            );
        }

        $this->saveFormUpdated('Exclude has been saved');
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $id = $this->dispatcher->getParam('id');

        $model = (new StatsExclude())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $id
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->softDelete();

            $this->addLog(
                'stat-excude',
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $this->saveFormDeleted('Stat excude has been marked as deleted');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
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
            'website/settings/index',
            [
                'data' => $model
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
        $model->name = $_POST['name'];
        $model->slogan = !empty($_POST['slogan']) ? $_POST['slogan'] : '';
        $model->address = !empty($_POST['address']) ? $_POST['address'] : '';
        $model->meta_description = !empty($_POST['meta_description']) ? $_POST['meta_description'] : '';
        $model->meta_author = !empty($_POST['meta_author']) ? $_POST['meta_author'] : '';
        $model->contact_email = !empty($_POST['contact_email']) ? $_POST['contact_email'] : '';
        $model->robots_txt = !empty($_POST['robots_txt']) ? $_POST['robots_txt'] : '';
        $model->robots = !empty($_POST['robots']) ? $_POST['robots'] : '';
        $model->humans_txt = !empty($_POST['humans_txt']) ? $_POST['humans_txt'] : '';
        $model->status = !empty($_POST['status']) ? 'online' : 'offline';
        $model->last_update = date('Y-m-d');

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

            $this->addStatsExclude();

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
