<?php

/**
 * Menu categories model.
 *
 * @package     Kytschi\Tengu\Models\Website\MenuCategories
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Menu;

class MenuCategories extends Model
{
    public $menu_id;
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
            Menu::class,
            'id',
            [
                'alias'    => 'category',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'page_id',
            Menu::class,
            'id',
            [
                'alias'    => 'item',
                'reusable' => true,
            ]
        );
    }
}
