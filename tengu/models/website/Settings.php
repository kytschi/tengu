<?php

/**
 * Settings model.
 *
 * @package     Kytschi\Tengu\Models\Website\Settings
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
    public $humans_txt;
    public $last_update;

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
