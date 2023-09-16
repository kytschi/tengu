<?php

/**
 * Settings model.
 *
 * @package     Kytschi\Akira\Models\Settings
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Akira\Models;

use Kytschi\Tengu\Models\Model;

class Settings extends Model
{
    public $webdav_url;
    public $webdav_auth;
    public $webdav_auth_two;

    protected $encrypted = [
        'webdav_url',
        'webdav_auth',
        'webdav_auth_two'
    ];

    public function initialize()
    {
        $this->setSource('akira_settings');
    }
}
