<?php

/**
 * Search engines traits.
 *
 * @package     Kytschi\Makabe\Traits\SearchEngines
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
