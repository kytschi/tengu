<?php

/**
 * Surveys controller.
 *
 * @package     Kytschi\Makabe\Controllers\SurveyStepsController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Makabe\Controllers;

use Kytschi\Makabe\Models\SurveyResponses;
use Kytschi\Makabe\Models\SurveySteps;
use Kytschi\Makabe\Models\UserSurveys;
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
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\Email;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class SurveyStepsController extends PagesController
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
    public $resource = 'survey-step';

    public static $types = [
        'input',
        'select',
        'slider',
        'contact form'
    ];

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->mms . $this->global_url;
    }

    public function addAction($template = 'makabe/surveys/steps/add')
    {
        $this->view->setVar('survey_id', $template);
        $this->view->setVar('types', self::$types);

        $this->setPageTitle('Creating a survey step');

        return parent::addAction('makabe/surveys/steps/add');
    }

    public function editAction($id, $template = 'makabe/surveys/steps/edit')
    {
        $this->view->setVar('survey_id', $id);
        $this->view->setVar('types', self::$types);

        $id = $template;

        $this->setPageTitle('Editing the survey step');
        return parent::editAction($id, 'makabe/surveys/steps/edit');
    }

    public function indexAction($template = 'makabe/surveys/index')
    {
        $this->setPageTitle('Our surveys');
        return parent::indexAction($template);
    }

    public function saveSubAction($model)
    {
        $step = new SurveySteps([
            'survey_id' => $_POST['survey_id'],
            'page_id' => $model->id,
            'type' => $_POST['type'],
            'required' => !empty($_POST['required']) ? 1 : 0,
            'options' => !empty($_POST['options']) ? $_POST['options'] : null,
            'range_min' => !empty($_POST['range_min']) ? intval($_POST['range_min']) : 0,
            'range_max' => !empty($_POST['range_max']) ? intval($_POST['range_max']) : 1,
            'range_steps' => !empty($_POST['range_steps']) ? intval($_POST['range_steps']) : 1
        ]);

        if ($step->save() === false) {
            throw new SaveException(
                'Failed to create the ' . str_replace('-', ' ', $this->resource),
                $model->getMessages()
            );
        }

        $this->redirect(
            UrlHelper::backend($this->global_url . '/' . $_POST['survey_id'] . '/steps/edit/' . $model->id)
        );
    }

    public function submitAction($survey_id, $id)
    {
        try {
            $this->saveFormData($_POST);

            $page = (new Pages())->findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $id
                ]
            ]);

            if (empty($page)) {
                throw new SaveException('Survey not found');
            }

            $url = $page->url;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $user = self::getUserIp();
            $key = !empty($_GET['ts']) ? $_GET['ts'] : '';
            $survey = (new UserSurveys())->findFirst([
                'conditions' => 'page_id = :page_id: AND key = :key: AND created_by = :created_by:',
                'bind' => [
                    'page_id' => $survey_id,
                    'key' => $key,
                    'created_by' => $user,
                ]
            ]);

            if (empty($survey)) {
                $survey = new UserSurveys([
                    'page_id' => $survey_id,
                    'current_step_id' => $id,
                    'key' => StringHelper::random(36),
                    'created_by' => $user,
                    'updated_by' => $user,
                ]);

                if ($survey->save() === false) {
                    throw new SaveException(
                        'Failed to create the survey',
                        $model->getMessages()
                    );
                }
            } elseif ($survey->status == 'complete') {
                $this->redirect($survey->survey->complete->url);
            }

            $secure = 0;

            if ($page->step->type == 'contact form') {
                $this->validateContact();

                if (!$this->tengu->captchaValidate($_POST['captcha'])) {
                    $url .= '?name=' . $_POST['name'];
                    $url .= '&method=' . (!empty($_POST['method']) ? $_POST['method'] : '');
                    $url .= '&time=' . (!empty($_POST['time']) ? $_POST['time'] : '');
                    $url .= '&phone=' . (!empty($_POST['phone']) ? $_POST['phone'] : '');
                    $url .= '&message=' . urlencode($_POST['message']);
                    $url .= '&email=' . urlencode($_POST['email']);

                    throw new ValidationException(
                        'Form validation failed, invalid captcha',
                        null,
                        400,
                        $url
                    );
                }

                $response = strip_tags($_POST['message']);
                $response .= "\n\nName: " . $_POST['name'];
                $response .= "\nEmail: " . $_POST['email'];
                $response .= "\nContact method: " . (!empty($_POST['method']) ? $_POST['method'] : 'not set');
                $response .= "\nTime: " . (!empty($_POST['time']) ? $_POST['time'] : 'not set');
                $response .= "\nPhone: " . (!empty($_POST['phone']) ? $_POST['phone'] : 'not set');

                $response = StringHelper::encrypt($response);
                $secure = 1;
            } elseif (empty($_POST[$id])) {
                throw new ValidationException(
                    'Form validation failed, please double check the required fields',
                    null,
                    400,
                    $url
                );
            } else {
                $response = $_POST[$survey->current_step_id];
            }

            $response_model = (new SurveyResponses())->findFirst([
                'conditions' => 'page_id = :page_id: AND survey_id = :survey_id: AND created_by = :created_by:',
                'bind' => [
                    'page_id' => $id,
                    'survey_id' => $survey->id,
                    'created_by' => $user,
                ]
            ]);

            if (empty($response_model)) {
                $response_model = new SurveyResponses([
                    'page_id' => $id,
                    'user_survey_id' => $survey->id,
                    'survey_id' => $survey_id,
                    'response' => $response,
                    'created_by' => $user,
                    'updated_by' => $user,
                    'secure' => $secure
                ]);

                if ($response_model->save() === false) {
                    throw new SaveException(
                        'Failed to save the survey response',
                        $response_model->getMessages()
                    );
                }
            } else {
                $response_model->response = $response;
                if ($response_model->update() === false) {
                    throw new SaveException(
                        'Failed to update the survey response',
                        $response_model->getMessages()
                    );
                }
            }

            $next = $page->step->next($page);
            if (!empty($next)) {
                $survey->current_step_id = $next->page_id;
            } else {
                $survey->current_step_id = null;
            }
            if (!$survey->current_step_id) {
                $survey->status = 'complete';
                $survey->completed_at = date('Y-m-d H:i:s');
            }
            if ($survey->update() === false) {
                throw new SaveException(
                    'Failed to update the survey',
                    $survey->getMessages()
                );
            }

            if ($survey->status == 'complete') {
                $this->redirect($survey->survey->complete->url);
            }

            $this->redirect(UrlHelper::append($survey->current->url, 'ts=' . $survey->key));
        } catch (Exception $err) {
            $this->saveFormError($err->getMessage());
            $page = (new Pages())->findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $survey_id
                ]
            ]);
            
            if (empty($page)) {
                throw new SaveException(
                    'Failed to process the survey',
                    $err->getMessage()
                );
            }

            $url = $page->url;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->redirect($url);
        }
    }

    public function updateSubAction($model)
    {
        $model->step->type = $_POST['type'];
        $model->step->required = !empty($_POST['required']) ? 1 : 0;
        $model->step->options = !empty($_POST['options']) ? $_POST['options'] : null;
        $model->step->range_min = !empty($_POST['range_min']) ? intval($_POST['range_min']) : 0;
        $model->step->range_max = !empty($_POST['range_max']) ? intval($_POST['range_max']) : 1;
        $model->step->range_steps = !empty($_POST['range_steps']) ? intval($_POST['range_steps']) : 1;

        if ($model->step->range_min < 0) {
            $model->step->range_min *= -1;
        }
        if ($model->step->range_max < 0) {
            $model->step->range_max *= -1;
        }
        if ($model->step->range_steps < 0) {
            $model->step->range_steps *= -1;
        } elseif (!$model->step->range_steps) {
            $model->step->range_steps = 1;
        }

        if ($model->step->range_min > $model->step->range_max) {
            $old_min = $model->step->range_min;
            $model->step->range_min = $model->step->range_max;
            $model->step->range_max = $old_min;
        } elseif ($model->step->range_min == $model->step->range_max) {
            $model->step->range_min = 0;
            $model->step->range_max = 1;
        }

        if ($model->step->update() === false) {
            throw new SaveException(
                'Failed to update the ' . str_replace('-', ' ', $this->resource),
                $model->step->getMessages()
            );
        }

        $this->redirect(
            UrlHelper::backend($this->global_url . '/' . $_POST['survey_id'] . '/steps/edit/' . $model->id)
        );
    }

    public function validate()
    {
        parent::validate();

        $validation = new Validation();

        $validation->add(
            'survey_id',
            new PresenceOf(
                [
                    'message' => 'The survey Id is required',
                ]
            )
        );

        $validation->add(
            'type',
            new PresenceOf(
                [
                    'message' => 'The type is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }

    private function validateContact()
    {
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
            'email',
            new PresenceOf(
                [
                    'message' => 'The email is required',
                ]
            )
        );

        $validation->add(
            'email',
            new Email(
                [
                    'message' => 'The email is not valid',
                ]
            )
        );

        $validation->add(
            'message',
            new PresenceOf(
                [
                    'message' => 'The message is required',
                ]
            )
        );

        $validation->add(
            'captcha',
            new PresenceOf(
                [
                    'message' => 'The CAPTCHA is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }

    public function viewAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Viewing the survey response');

        $model = (new UserSurveys())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        return $this->view->partial(
            'makabe/surveys/view',
            [
                'data' => $model
            ]
        );
    }
}
