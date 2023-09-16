<?php

/**
 * Themes model.
 *
 * @package     Kytschi\Tengu\Models\Website\Themes
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;

class Themes extends Model
{
    public $name;
    public $slug;
    public $slogan;
    public $folder;
    public $default;
    public $status;
    public $active_from;
    public $active_to;
    public $annual;

    public function initialize()
    {
        $this->hasMany(
            'id',
            Logs::class,
            'resource_id',
            [
                'alias'    => 'logs',
                'reusable' => true,
                'params'   => [
                    'order' => 'created_at DeSC'
                ]
            ]
        );
    }
}
