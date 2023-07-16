<?php

/**
 * Surveys model.
 *
 * @package     Kytschi\Makabe\Models\Surveys
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Makabe\Models;

use Kytschi\Makabe\Models\SurveyResponses;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Model;

class Surveys extends Model
{
    public $page_id;
    public $complete_page_id;

    public function initialize()
    {
        $this->setSource('makabe_surveys');

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
                'alias'    => 'page'
            ]
        );

        $this->hasOne(
            'complete_page_id',
            Pages::class,
            'id',
            [
                'alias'    => 'complete'
            ]
        );
    }
}
