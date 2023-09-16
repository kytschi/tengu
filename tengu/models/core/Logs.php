<?php

/**
 * Logs model.
 *
 * @package     Kytschi\Tengu\Models\Core\Logs
 * @copyright   2021 Kytschi
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Models\Core;

use Kytschi\Tengu\Models\Model;

class Logs extends Model
{
    public $resource;
    public $resource_id;
    public $summary;
    public $content;
    public $type = 'info';

    public function initialize()
    {
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
    }

    public function getResourceUrl()
    {
        $url = '';

        if ($this->resource_id) {
            switch ($this->resource) {
                case 'blog-post':
                    $url = ($this->di->getConfig())->urls->cms . '/blog-posts/edit/' . $this->resource_id;
                    break;
                case 'page':
                    $url = ($this->di->getConfig())->urls->cms . '/pages/edit/' . $this->resource_id;
                    break;
                case 'portfolio':
                    $url = ($this->di->getConfig())->urls->cms . '/portfolio/edit/' . $this->resource_id;
                    break;
            }
        }

        return $url;
    }
}
