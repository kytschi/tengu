<?php

/**
 * Stats data model - used for global stats.
 *
 * @package     Kytschi\Tengu\Models\Website\StatsData
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Tengu\Models\Model;

class StatsData extends Model
{
    public $country;
    public $total = 0;
}
