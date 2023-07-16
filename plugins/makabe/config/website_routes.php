<?php

/**
 * Front end routes.
 *
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
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
