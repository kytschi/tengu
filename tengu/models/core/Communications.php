<?php

/**
 * Communications model.
 *
 * @package     Kytschi\Tengu\Models\Core\Communications
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Models\Core;

use Kytschi\Tengu\Models\Model;

class Communications extends Model
{
    public $box = 'inbox';
    public $type = 'email';
    public $subject;
    public $message;
    public $from_name;
    public $from_email;
    public $from_phone;
    public $to_name;
    public $to_email;
    public $status = 'sending';

    protected $encrypted = [
        'subject',
        'message',
        'from_name',
        'from_email',
        'from_phone',
    ];
}
