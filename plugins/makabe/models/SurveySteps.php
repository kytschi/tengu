<?php

/**
 * Survey steps model.
 *
 * @package     Kytschi\Makabe\Models\SurveySteps
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Makabe\Models;

use Kytschi\Makabe\Models\UserSurveys;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Model;

class SurveySteps extends Model
{
    public $survey_id;
    public $page_id;
    public $type = 'input';
    public $options;
    public $range_min = 0;
    public $range_max = 1;
    public $range_steps = 1;
    public $required = 0;

    public function initialize()
    {
        $this->setSource('makabe_survey_steps');

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
    }

    public function getName()
    {
        return $this->page->name;
    }

    public function getUserSurvey()
    {
        return (new UserSurveys())->findFirst([
            'conditions' => 'page_id = :page_id: AND key = :key: AND created_by = :created_by:',
            'bind' => [
                'page_id' => $this->survey_id,
                'key' => !empty($_GET['ts']) ? $_GET['ts'] : '',
                'created_by' => self::getUserIp()
            ]
        ]);
    }

    public function next($current)
    {
        $next = false;
        foreach ($this->survey->steps as $step) {
            if ($next) {
                return $step;
            }
            if ($step->page_id == $current->id) {
                $next = true;
            }
        }

        return new Pages();
    }

    public function prev($current)
    {
        $prev = $current;
        foreach ($this->survey->steps as $step) {
            if ($step->page_id == $current->id) {
                return $prev;
            }
            $prev = $step;
        }

        return new Pages();
    }
}
