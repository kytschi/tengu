<?php

/**
 * Events traits.
 *
 * @package     Kytschi\Izumi\Traits\Events
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Izumi\Traits;

use Kytschi\Tengu\Models\Website\Pages;

trait Events
{
    public function findEvents($data = [])
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
            'conditions' => 'type = "event" AND deleted_at IS NULL ' . $query,
            'bind' => $bind
        ]);
    }

    public function findFeaturedEvents($data = [])
    {
        $query = '';
        $binds = [];

        if (!empty($data['random'])) {
            $order = 'RAND() LIMIT ' . intval($data['random']);
        } else {
            $order = 'created_at';
        }

        return Pages::find([
            'conditions' => 'type in ("event", "event-category") AND feature = 1 AND deleted_at IS NULL ' . $query,
            'bind' => $binds,
            'order' => $order
        ]);
    }
}
