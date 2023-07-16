<?php

/**
 * Page categories model.
 *
 * @package     Kytschi\Tengu\Models\Website\PageCategories
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Tengu\Models\Core\Categories;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Pages;

class PageCategories extends Model
{
    public $page_id;
    public $category_id;
    public $sort = 0;

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
            'category_id',
            Pages::class,
            'id',
            [
                'alias'    => 'category',
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
