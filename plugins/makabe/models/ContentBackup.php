<?php

/**
 * Content backup model.
 *
 * @package     Kytschi\Makabe\Models\ContentBackup
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Makabe\Models;

use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Website\Stats;

class ContentBackup extends Model
{
    public $resource;
    public $resource_id;
    public $content;
    public $title;
    public $url;
    public $canonical_url;
    public $meta_description;

    public function initialize()
    {
        $this->setSource('makabe_spin_content');

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

        $this->hasMany(
            'id',
            Logs::class,
            'resource_id',
            [
                'alias'    => 'logs',
                'reusable' => true,
                'params'   => [
                    'order' => 'created_at DESC'
                ]
            ]
        );

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
