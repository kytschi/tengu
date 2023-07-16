<?php

/**
 * Page files model.
 *
 * @package     Kytschi\Tengu\Models\Website\PageFiles
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Pages;

class PageFiles extends Model
{
    public $page_id;
    public $file_id;
    public $resource;

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
            'file_id',
            Files::class,
            'id',
            [
                'alias'    => 'file',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'page_id',
            Pages::class,
            'id',
            [
                'alias'    => 'page',
                'reusable' => true,
            ]
        );
    }
}
