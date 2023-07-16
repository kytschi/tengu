<?php

/**
 * Generic model.
 *
 * @package     Kytschi\Tengu\Models\Model
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright 2022 Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Models;

use Kytschi\Tengu\Controllers\Core\UsersController;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Security;
use Kytschi\Tengu\Traits\Core\User;
use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Encryption\Security\Random;

class Model extends PhalconModel
{
    use Security;
    use User;

    public $id;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;
    public $deleted_at;
    public $deleted_by;

    protected $system_uuid = '00000000-0000-0000-0000-000000000000';

    protected $encrypted = [];

    public function afterFetch()
    {
        $this->decryptData();
    }

    public function beforeValidationOnCreate()
    {
        if (empty($this->id)) {
            $this->id = (new Random())->uuid();
        }

        if (empty($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        $this->updated_at = date('Y-m-d H:i:s');

        $user_id = self::getUserId();
        if (empty($this->created_by)) {
            $this->created_by = $user_id;
        }

        if (empty($this->updated_by)) {
            $this->updated_by = $user_id;
        }

        foreach ($this->encrypted as $key) {
            if (!property_exists($this, $key)) {
                continue;
            }
            $this->$key = self::encrypt($this->$key);
        }
    }

    public function beforeValidationOnUpdate()
    {
        $this->updated_at = date('Y-m-d H:i:s');
        $this->updated_by = self::getUserId();
        
        foreach ($this->encrypted as $key) {
            if (!property_exists($this, $key)) {
                continue;
            }
            if ($this->hasChanged($key)) {
                $this->$key = self::encrypt($this->$key);
            }
        }
    }

    public function onConstruct()
    {
        if ($this->encrypted) {
            $this->keepSnapshots(true);
        }
    }
    
    public function clone($data = [])
    {
        $tmp = [];
        foreach ($this as $key => $value) {
            if ($key == 'id') {
                $value = null;
            }
            $tmp[$key] = $value;
        }

        return PhalconModel::cloneResult(
            new $this(),
            array_merge($tmp, $data)
        );
    }

    public function decryptByKey($key)
    {
        return self::decrypt($this->$key);
    }

    public function decryptData()
    {
        if ($this->encrypted) {
            foreach ($this->encrypted as $key) {
                if (!empty($this->$key)) {
                    $this->$key = self::decrypt($this->$key);
                }
            }
        }
    }

    public function getConfig()
    {
        return (new UsersController())->config;
    }

    public function getSystemUser()
    {
        return new Users([
            'id' => $this->system_uuid,
            'first_name' => 'Tengu',
        ]);
    }

    public function recover(bool $ignore_status = false)
    {
        if (property_exists($this, 'status') && !$ignore_status) {
            $this->status = 'active';
        }
        $this->deleted_at = null;
        $this->deleted_by = null;
        $this->save();
    }

    public function softDelete(string $status = 'deleted', bool $ignore_status = false)
    {
        if (property_exists($this, 'status') && !$ignore_status) {
            $this->status = $status;
        }

        $this->deleted_at = date('Y-m-d H:i:s');
        $this->deleted_by = self::getUserId();
        $this->save();
    }
}
