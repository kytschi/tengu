<?php

/**
 * Notifications model.
 *
 * @package     Kytschi\Tengu\Models\Core\Notifications
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Models\Core;

use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Core\Users;

class Notifications extends Model
{
    public $from_user_id;
    public $to_user_id;
    public $subject;
    public $content;
    public $url = null;

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
            'to_user_id',
            Users::class,
            'id',
            [
                'alias'    => 'to',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'from_user_id',
            Users::class,
            'id',
            [
                'alias'    => 'from_user',
                'reusable' => true
            ]
        );
    }

    public function getFrom()
    {
        if ($this->from_user_id == $this->system_uuid) {
            return $this->getSystemUser();
        } else {
            return $this->from_user;
        }
    }
}
