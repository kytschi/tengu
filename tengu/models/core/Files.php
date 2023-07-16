<?php

/**
 * Files model.
 *
 * @package     Kytschi\Tengu\Models\Core\Files
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Models\Core;

use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Core\FileDownloadHistory;
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
