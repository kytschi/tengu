<?php

/**
 * Search engines traits.
 *
 * @package     Kytschi\Makabe\Traits\SearchEngines
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Makabe\Traits;

trait SearchEngines
{
    public $search_engines = [
        [
            'id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'Google',
            'url' => 'https://google.com/search'
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'name' => 'Google UK',
            'url' => 'https://google.co.uk/search'
        ]
    ];
}
