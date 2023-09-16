<?php

/**
 * Communications model.
 *
 * @package     Kytschi\Tengu\Models\Core\Communications
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
