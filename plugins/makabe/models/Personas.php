<?php

/**
 * Personas model.
 *
 * @package     Kytschi\Makabe\Models\Personas
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Makabe\Models;

use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;

class Personas extends Model
{
    public $default = 0;
    public $title;
    public $first_name;
    public $last_name;
    public $job_title;
    public $email;
    public $phone_number;
    public $mobile_number;
    public $status = 'active';

    public function initialize()
    {
        $this->setSource('makabe_personas');

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
            Notes::class,
            'resource_id',
            [
                'alias'    => 'notes',
                'reusable' => true,
                'params'   => [
                    'order' => 'created_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Tags::class,
            'resource_id',
            [
                'alias'    => 'tags',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'tag'
                ]
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

        $this->hasOne(
            'id',
            Files::class,
            'resource_id',
            [
                'alias'    => 'profile_image',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL AND resource="profile-image"'
                ]
            ]
        );
    }

    public function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getInitials()
    {
        return substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1);
    }
}
