<?php

/**
 * Survey responses model.
 *
 * @package     Kytschi\Makabe\Models\SurveyResponses
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Makabe\Models;

use Kytschi\Makabe\Models\SurveySteps;
use Kytschi\Makabe\Models\UserSurveys;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Model;

class SurveyResponses extends Model
{
    public $page_id;
    public $survey_id;
    public $user_survey_id;

    public function initialize()
    {
        $this->setSource('makabe_survey_responses');

        $this->hasOne(
            'updated_by',
            Users::class,
            'id',
            [
                'alias'    => 'updated',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'created_by',
            Users::class,
            'id',
            [
                'alias'    => 'created',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'deleted_by',
            Users::class,
            'id',
            [
                'alias'    => 'deleted',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'page_id',
            Pages::class,
            'id',
            [
                'alias'    => 'page',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'survey_id',
            Pages::class,
            'id',
            [
                'alias'    => 'survey',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'user_survey_id',
            UserSurveys::class,
            'id',
            [
                'alias'    => 'user_survey',
                'reusable' => true
            ]
        );
    }
}
