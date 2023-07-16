<?php

/**
 * Surveys controller.
 *
 * @package     Kytschi\Makabe\Controllers\SurveysController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Makabe\Controllers;

use Kytschi\Makabe\Models\Surveys;
use Kytschi\Tengu\Controllers\Website\PagesController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Encryption\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class SurveysController extends PagesController
{
    use Files;
    use Form;
    use Logs;
    use Notes;
    use Pagination;

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
    }

    public function addAction($template = 'makabe/surveys/add')
    {
        $this->setPageTitle('Creating a survey');
        $this->view->setVar('pages', (new Pages())->find([
            'conditions' => 'deleted_at IS NULL',
            'order' => 'name ASC'
        ]));
        return parent::addAction($template);
    }

    public function editAction($id, $template = 'makabe/surveys/edit')
    {
        $this->setPageTitle('Editing the survey');
        $this->view->setVar('pages', (new Pages())->find([
            'conditions' => 'deleted_at IS NULL AND type IN ("page")',
            'order' => 'name ASC'
        ]));
        return parent::editAction($id, $template);
    }

    public function indexAction($template = 'makabe/surveys/index')
    {
        $this->setPageTitle('Our surveys');
        return parent::indexAction($template);
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
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
