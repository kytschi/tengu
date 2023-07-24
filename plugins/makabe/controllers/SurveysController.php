<?php

/**
 * Surveys controller.
 *
 * @package     Kytschi\Makabe\Controllers\SurveysController
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

namespace Kytschi\Makabe\Controllers;

use Kytschi\Makabe\Models\Surveys;
use Kytschi\Tengu\Controllers\Website\PagesController;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Models\Website\Pages;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class SurveysController extends PagesController
{
    public $access = [
        'administrator',
        'super-user',
        'seo-manager',
        'marketing-manager'
    ];

    public $global_url = '/surveys';
    public $resource = 'survey';

    public static $types = [
        'Public',
        'Internal',
        'Email'
    ];

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->mms . $this->global_url;
        $this->global_add_url = $this->global_url . '/create';
    }

    public function addAction($title = 'Creating a survey', $template = 'website/pages/add')
    {
        $this->view->setVar('pages', (new Pages())->find([
            'conditions' => 'deleted_at IS NULL',
            'order' => 'name ASC'
        ]));
        return parent::addAction($title, $template);
    }

    public function editAction($id, $title = 'Editing the survey', $template = 'makabe/surveys/edit')
    {
        $this->view->setVar('pages', (new Pages())->find([
            'conditions' => 'deleted_at IS NULL AND type IN ("page")',
            'order' => 'name ASC'
        ]));
        return parent::editAction($id, $title, $template);
    }

    public function indexAction($title = 'Our surveys', $template = 'makabe/surveys/index')
    {
        return parent::indexAction($title, $template);
    }

    public function saveSubAction($model)
    {
        $survey = new Surveys([
            'page_id' => $model->id,
            'complete_page_id' => $_POST['complete_page_id']
        ]);

        if ($survey->save() === false) {
            throw new SaveException(
                'Failed to create the ' . str_replace('-', ' ', $this->resource),
                $survey->getMessages()
            );
        }
    }

    public function updateSubAction($model)
    {
        $model->survey->complete_page_id = $_POST['complete_page_id'];

        if ($model->survey->update() === false) {
            throw new SaveException(
                'Failed to create the ' . str_replace('-', ' ', $this->resource),
                $model->survey->getMessages()
            );
        }
    }

    public function validate()
    {
        parent::validate();

        $validation = new Validation();

        $validation->add(
            'complete_page_id',
            new PresenceOf(
                [
                    'message' => 'The complete page is required',
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
