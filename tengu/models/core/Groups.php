<?php

/**
 * Groups model.
 *
 * @package     Kytschi\Tengu\Models\Core\Groups
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Models\Core;

use Kytschi\Tengu\Models\Model;

class Groups extends Model
{
    public $name;
    public $slug;
    public $system = false;
    public $status = 'active';
}
