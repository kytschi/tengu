<?php

/**
 * Files model.
 *
 * @package     Kytschi\Tengu\Models\Core\Files
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
    public $url = '';
    public $output_url = '';
    public $thumb_url = '';
    public $download_url = '';
    public $location = '';

    protected $transform_map = [
        'id',
        'mime_type',
        'name',
        'label',
        'filename',
        'url',
        'output_url',
        'thumb_url',
        'download_url',
        'tags'
    ];

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

    public function afterFetch()
    {
        parent::afterFetch();
        $this->url = ($this->getConfig())->application->dumpUri . $this->filename;
        $this->output_url  = ($this->getConfig())->application->outputFileUri .
            '/' .
            urlencode(self::encrypt($this->id));
        $this->thumb_url = ($this->getConfig())->application->dumpUri . 'thumb-' . $this->filename;
        $this->location = ($this->getConfig())->application->dumpDir . $this->filename;
        $this->download_url = ($this->getConfig())->application->downloadFileUri .
            '/' .
            urlencode(self::encrypt($this->id));
    }
}
