<?php

/**
 * Menu model.
 *
 * @package     Kytschi\Tengu\Models\Website\Menu
 * @copyright   2021 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Pages;

class Menu extends Model
{
    public $page_id;
    public $name;
    public $tooltip;
    public $sort;
    public $external_link;
    public $new_window = false;
    public $target = 'main';
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
