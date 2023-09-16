<?php

/**
 * Products traits.
 *
 * @package     Kytschi\Phoenix\Traits\Products
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Phoenix\Traits;

use Kytschi\Tengu\Models\Website\Pages;

trait Products
{
    public function findProducts($data = [])
    {
        $query = '';
        $bind = [];

        if (!empty($data)) {
            if (!empty($data['where'])) {
                if (!empty($data['where']['query'])) {
                    $query = 'AND ' . $data['where']['query'];
                }

                if (!empty($data['where']['data'])) {
                    $bind = $data['where']['data'];
                }
            }
        }

        return Pages::find([
            'conditions' => 'type = "product" AND deleted_at IS NULL ' . $query,
            'bind' => $bind
        ]);
    }
}
