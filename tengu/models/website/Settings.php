<?php

/**
 * Settings model.
 *
 * @package     Kytschi\Tengu\Models\Website\Settings
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301, USA.
 */

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Phoenix\Models\Settings as Sales;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Website\StatsExclude;
use Kytschi\Wako\Models\Settings as Finance;
use Phalcon\Crypt;

class Settings extends Model
{
    public $id = '00000000-0000-0000-0000-000000000000';
    public $name;
    public $slogan;
    public $address;
    public $meta_description;
    public $meta_keywords;
    public $meta_author;
    public $contact_email;
    public $robots_txt;
    public $robots;
    public $status = 'online';
    public $tengu_theme = 'default';
    public $cache_key;

    public function initialize()
    {
        $this->hasMany(
            'id',
            Tags::class,
            'resource_id',
            [
                'alias'    => 'tags',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL AND resource="settings"',
                    'order' => 'tag'
                ]
            ]
        );
    }

    public function getCompressKey()
    {
        return (new Crypt())->decrypt(
            urldecode($this->cache_key),
            "VlRiTGozdGFCcHl0cFZIMUxndjhYcUVEUEE5ZE9TUExvdGhtRTZCSXg0aEdLdFljTW40MTk5UWJWYWtmbEJWK3F4dGUrehgng744kDHDKFHy7t745hdy7Sh34734ksdnlsd0DHHSLDFSYDSNHd"
        );
    }

    public function getExcludes()
    {
        return (new StatsExclude())->find([
            'conditions' => 'deleted_at IS NULL'
        ]);
    }

    public function getFinance()
    {
        return (new Finance())->findFirst([
            'id IS NOT NULL',
            [
                'reusable' => true,
            ]
        ]);
    }

    public function getLogs()
    {
        return (new Logs())->find([
            'conditions' => 'resource = "settings"'
        ]);
    }

    public function getSales()
    {
        return (new Sales())->findFirst([
            'id IS NOT NULL',
            [
                'reusable' => true,
            ]
        ]);
    }
}
