<?php

/**
 * Surveys model.
 *
 * @package     Kytschi\Makabe\Models\UserSurveys
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

namespace Kytschi\Makabe\Models;

use Kytschi\Makabe\Models\SurveyResponses;
use Kytschi\Makabe\Models\Surveys;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Model;

class UserSurveys extends Model
{
    public $page_id;
    public $key;
    public $current_step_id;
    public $status = 'pending';

    public function initialize()
    {
        $this->setSource('makabe_user_surveys');

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
            'current_step_id',
            Pages::class,
            'id',
            [
                'alias' => 'current'
            ]
        );

        $this->hasOne(
            'current_step_id',
            SurveyResponses::class,
            'page_id',
            [
                'alias' => 'current_response'
            ]
        );

        $this->hasOne(
            'page_id',
            Surveys::class,
            'page_id',
            [
                'alias'    => 'survey'
            ]
        );

        $this->hasMany(
            'id',
            SurveyResponses::class,
            'survey_id',
            [
                'alias'    => 'responses',
                'reusable' => true,
                'params'   => [
                    'order' => 'created_at'
                ]
            ]
        );
    }

    public function getCompletionTime()
    {
        if (!$this->created_at || !$this->completed_at) {
            return null;
        }

        $start = new \DateTimeImmutable($this->created_at);
        $end = new \DateTimeImmutable($this->completed_at);
        $interval = $start->diff($end);

        return $interval->format('%im %ss');
    }

    public function response($page_id)
    {
        return (new SurveyResponses())->findFirst([
            'conditions' => 'page_id = :page_id: AND survey_id = :survey_id: AND user_survey_id = :user_survey_id:',
            'bind' => [
                'survey_id' => $this->page_id,
                'page_id' => $page_id,
                'user_survey_id' => $this->id
            ]
        ]);
    }
}
