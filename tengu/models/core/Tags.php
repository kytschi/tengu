<?php

/**
 * Tags model.
 *
 * @package     Kytschi\Tengu\Models\Core\Tags
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Models\Core;

use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Model;

class Tags extends Model
{
    public $resource;
    public $resource_id;
    public $tag;
    public $type;

    public function initialize()
    {
        $this->hasOne(
            'resource_id',
            Files::class,
            'id',
            [
                'alias'    => 'file',
                'reusable' => true,
            ]
        );
    }
}
