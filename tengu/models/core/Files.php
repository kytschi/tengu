<?php

/**
 * Files model.
 *
 * @package     Kytschi\Tengu\Models\Core\Files
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

namespace Kytschi\Tengu\Models\Core;

use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Core\FileDownloadHistory;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;

class Files extends Model
{
    public $resource;
    public $resource_id;
    public $mime_type;
    public $name;
    public $label;
    public $filename;
    public $status;
    public $compress = false;

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

        $this->hasMany(
            'id',
            FileDownloadHistory::class,
            'file_id',
            [
                'alias'    => 'download_history',
                'reusable' => true
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
                    'conditions' => 'deleted_at IS NULL AND resource="file" AND type IS NULL',
                    'order' => 'tag'
                ]
            ]
        );
    }

    public function getDownloadUrl()
    {
        return ($this->getConfig())->application->downloadFileUri . '/' . urlencode(self::encrypt($this->id));
    }

    public function getLocation()
    {
        return ($this->getConfig())->application->dumpDir . $this->filename;
    }

    public function getUrl()
    {
        return ($this->getConfig())->application->dumpUri . $this->filename;
    }

    public function getOutputUrl()
    {
        return ($this->getConfig())->application->outputFileUri . '/' . urlencode(self::encrypt($this->id));
    }

    public function getThumbUrl()
    {
        return ($this->getConfig())->application->dumpUri . 'thumb-' . $this->filename;
    }
}
