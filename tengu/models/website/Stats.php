<?php

/**
 * Stats model.
 *
 * @package     Kytschi\Tengu\Models\Website\Stats
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
