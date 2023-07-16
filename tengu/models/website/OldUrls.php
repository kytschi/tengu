<?php

/**
 * Old Urls model.
 *
 * @package     Kytschi\Tengu\Models\Website\OldUrls
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
