<?php

/**
 * Stats model.
 *
 * @package     Kytschi\Tengu\Models\Website\Stats
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Pages;

class Stats extends Model
{
    public $resource;
    public $resource_id;
    public $parent_id;
    public $visitor;
    public $referer;
    public $bot;
    public $agent;
    public $browser;
    public $operating_system;

    protected $encrypted = [
        'visitor'
    ];

    public function initialize()
    {
        $this->hasOne(
            'resource_id',
            Pages::class,
            'id',
            [
                'alias'    => 'page',
                'reusable' => true,
                'conditions' => 'resource="page"'
            ]
        );
    }
}
