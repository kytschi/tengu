<?php

/**
 * Old Urls model.
 *
 * @package     Kytschi\Tengu\Models\Website\OldUrls
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Pages;

class OldUrls extends Model
{
    public $resource_id;
    public $resource;
    public $url;
    public $status = 'active';

    public function initialize()
    {
        $this->hasOne(
            'resource_id',
            Pages::class,
            'id',
            [
                'alias'    => 'page',
                'reusable' => true,
            ]
        );
    }
}
