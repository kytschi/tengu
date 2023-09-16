<?php

/**
 * Reviews traits.
 *
 * @package     Kytschi\Tengu\Traits\Website\Reviews
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Traits\Website;

use Kytschi\Tengu\Models\Website\Pages;

trait Reviews
{
    public function findReviews($data)
    {
        if (empty($data)) {
            return null;
        }

        $binds = [];
        if (!empty($data['page'])) {
            $binds['id'] = $data['page'];
        }

        $order = 'rating DESC';
        if (!empty($data['random'])) {
            $order = ' RAND() LIMIT ' . intval($data['random']);
        }

        return Pages::find([
            'conditions' => 'type="review"',
            'bind' => $binds,
            'order' => $order
        ]);
    }
}
