<?php

/**
 * Search stats model.
 *
 * @package     Kytschi\Tengu\Models\Website\SearchStats
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Tengu\Models\Model;

class SearchStats extends Model
{
    public $type = 'internal';
    public $visitor;
    public $query;
    public $referer;
}
