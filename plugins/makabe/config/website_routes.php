<?php

/**
 * Front end routes.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

/**
 * Surveys
 */
$router->add(
    '/survey/{survey_id}/steps/submit/{id}',
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SurveySteps',
        'action'     => 'submit',
        'survey_id' => 1,
        'id' => 2
    ]
);
