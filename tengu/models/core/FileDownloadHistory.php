<?php

/**
 * File download history model.
 *
 * @package     Kytschi\Tengu\Models\Core\FileDownloadHistory
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Models\Core;

use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Core\Users;

class FileDownloadHistory extends Model
{
    public $file_id;
    public $summary;

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
    }
}
