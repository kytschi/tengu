<?php

/**
 * Events controller.
 *
 * @package     Kytschi\Izumi\Controllers\EventsController
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

namespace Kytschi\Izumi\Controllers;

use Kytschi\Tengu\Controllers\Website\PagesController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class EventsController extends PagesController
{
    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/events';

    public $resource = 'event';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->ems . $this->global_url;
    }

    public function saveSubAction($model)
    {
        $this->subValidate();

        $model->event_on = DateHelper::sql($_POST['event_on']);
        $model->event_length = $_POST['event_length'];

        if ($model->update() === false) {
            throw new SaveException(
                'Failed to update the ' . str_replace('-', ' ', $this->resource),
                $model->getMessages()
            );
        }
    }

    public function subValidate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            'event_on',
            new PresenceOf(
                [
                    'message' => 'The event date is required',
                ]
            )
        );

        $validation->add(
            'event_length',
            new PresenceOf(
                [
                    'message' => 'The event length is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }

    public function updateSubAction($model)
    {
        $this->subValidate();

        $model->event_on = DateHelper::sql($_POST['event_on']);
        $model->event_length = $_POST['event_length'];

        if ($model->update() === false) {
            throw new SaveException(
                'Failed to update the ' . str_replace('-', ' ', $this->resource),
                $model->getMessages()
            );
        }
    }
}
