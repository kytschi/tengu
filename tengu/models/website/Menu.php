<?php

/**
 * Menu model.
 *
 * @package     Kytschi\Tengu\Models\Website\Menu
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\MenuCategories;
use Kytschi\Tengu\Models\Website\Pages;

class Menu extends Model
{
    public $page_id;
    public $slug;
    public $name;
    public $tooltip;
    public $sort;
    public $external_link;
    public $new_window = false;
    public $status;

    public function initialize()
    {
        $this->hasOne(
            'page_id',
            Pages::class,
            'id',
            [
                'alias'    => 'page',
                'reusable' => true,
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
                    'order' => 'created_at DeSC'
                ]
            ]
        );

        $this->hasManyToMany(
            'id',
            MenuCategories::class,
            'menu_id',
            'category_id',
            self::class,
            'id',
            [
                'alias'    => 'categories',
                'reusable' => true,
                'params'   => [
                    'conditions' => MenuCategories::class . '.deleted_at IS NULL AND ' .
                        Menu::class . '.deleted_at IS NULL',
                    'order' => MenuCategories::class . '.sort ASC'
                ]
            ]
        );

        $this->hasManyToMany(
            'id',
            MenuCategories::class,
            'category_id',
            'menu_id',
            self::class,
            'id',
            [
                'alias'    => 'category_items',
                'reusable' => true,
                'params'   => [
                    'conditions' => MenuCategories::class . '.deleted_at IS NULL AND ' .
                        Menu::class . '.deleted_at IS NULL',
                    'order' => MenuCategories::class . '.sort ASC'
                ]
            ]
        );
    }

    public function getLink()
    {
        if (!empty($this->page)) {
            return $this->page->real_url;
        } else {
            return $this->external_link;
        }
    }
}
