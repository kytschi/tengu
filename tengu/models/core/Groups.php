<?php

/**
 * Groups model.
 *
 * @package     Kytschi\Tengu\Models\Core\Groups
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
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
