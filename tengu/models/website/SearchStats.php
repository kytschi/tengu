<?php

/**
 * Search stats model.
 *
 * @package     Kytschi\Tengu\Models\Website\SearchStats
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
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
