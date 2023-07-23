<?php

/**
 * Pages controller.
 *
 * @package     Kytschi\Akira\Controllers\AppointmentsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
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

namespace Kytschi\Akira\Controllers;

use Kytschi\Akira\Models\Appointments;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Tengu\Traits\Core\User;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class AppointmentsController extends ControllerBase
{
    use Form;
    use Logs;
    use Notes;
    use Tags;
    use User;

    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url = '/appointments';

    public $resource = 'appointment';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->css . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Create an appointment');

        return $this->view->partial(
            'akira/appointments/add',
            [
                'url' => $this->global_url,
                'resource' => $this->resource,
                'users' => Users::find([
                    'conditions' => 'type IN ("user") AND id != :id:',
                    'bind' => [
                        'id' => self::getUserId()
                    ]
                ])
            ]
        );
    }

    public function cancelAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Appointments())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->status = 'cancelled';
            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the appointment',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Cancelled by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormWarning('Appointment has been cancelled');
            $this->redirect(
                UrlHelper::backend(
                    rtrim($url, '/')
                )
            );
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Appointments())->findFirst([
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

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Appointment has been deleted');
            $this->redirect(
                UrlHelper::backend(
                    rtrim($url, '/')
                )
            );
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function editAction($id)
    {
        $this->secure($this->access);

        $model = (new Appointments())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle('Editing the appointment');

        return $this->view->partial(
            'akira/appointments/edit',
            [
                'data' => $model,
                'url' => $this->global_url,
                'resource' => $this->resource
            ]
        );
    }

    public function indexAction()
    {
        $this->setPageTitle('Our appointments');

        $this->clearFormData();
        $this->secure($this->access);
        $this->setFilters();

        if (!empty($_GET['date'])) {
            $date = $_GET['date'];
        } else {
            $date = date('Y-m');
        }

        $results = Appointments::find([
            'conditions' => 'appointment_at BETWEEN :start: AND :end:',
            'bind' => [
                'start' => $date . '-01',
                'end' => $date . '-31'
            ]
        ]);

        $data = [];
        if ($results) {
            foreach ($results as $result) {
                $day = intval(date('d', strtotime($result->appointment_at)));
                if (empty($data[$day])) {
                    $data[$day] = [];
                }
                $data[$day][] = $result;
            }
        }

        return $this->view->partial(
            'akira/appointments/index',
            [
                'date' => $date,
                'url' => $this->global_url,
                'data' => $data,
                'stats' => $this->stats()
            ]
        );
    }

    public function rebookAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Appointments())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->status = 'booked';
            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the appointment',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Rebooked by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Appointment has been rebooked');
            $this->redirect(
                UrlHelper::backend(
                    rtrim($url, '/')
                )
            );
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Appointments())->findFirst([
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

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Appointment has been recovered');
            $this->redirect(
                UrlHelper::backend(rtrim($url, '/'))
            );
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function saveAction($ignore = false)
    {
        $this->secure($this->access);

        try {
            $this->validate();

            $model = $this->setData(new Appointments());

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the appointment',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved('Appointment has been saved');
            $this->clearFormData();

            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/create'));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function setData($model)
    {
        $model->name = $_POST['name'];
        $model->user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : self::getUserId();
        $model->status = isset($_POST['status']) ? 'free' : 'booked';
        $model->appointment_at = DateHelper::sql($_POST['appointment_at']);
        $model->recurring = isset($_POST['recurring']) ?
            ($_POST['recurring'] == 'none' ? null : $_POST['recurring']) :
            null;

        return $model;
    }

    public function stats()
    {
        $table = (new Appointments())->getSource();
        $query = 'SELECT ';
        $query .= "(
            SELECT count(id) FROM $table WHERE status = 'booked'
        ) AS booked,";
        $query .= "(
            SELECT count(id) FROM $table WHERE status = 'free'
        ) AS free,";
        $query .= "(
            SELECT count(id) FROM $table WHERE status = 'cancelled'
        ) AS cancelled,";
        $query .= "(
            SELECT count(id) FROM $table WHERE deleted_at IS NOT NULL
        ) AS deleted";

        $model = new Appointments();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function updateAction($ignore = false)
    {
        $this->secure($this->access);

        $model = (new Appointments())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $url = UrlHelper::backend($this->global_url . '/edit/' . $model->id);
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        try {
            $this->validate();

            $model = $this->setData($model);
            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the appointment',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Appointment has been updated');
            $this->clearFormData();

            $this->redirect($url);
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($url));
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
            'name',
            new PresenceOf(
                [
                    'message' => 'The name is required',
                ]
            )
        );

        $validation->add(
            'appointment_at',
            new PresenceOf(
                [
                    'message' => 'The date & time at is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException(
                'Form validation failed, please double check the required fields',
                $messages
            );
        }
    }
}
