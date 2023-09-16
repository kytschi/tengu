<?php

/**
 * Menu traits.
 *
 * @package     Kytschi\Tengu\Traits\Website\Menu
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Traits\Website;

use Kytschi\Tengu\Models\Website\Menu as Model;

trait Menu
{
    public function findMenu($data)
    {
        if (empty($data)) {
            return null;
        }

        $binds = [];
        $query = '';
        if (!empty($data['id'])) {
            $query = 'id = :id:';
            $binds = ['id' => $data['id']];
        }

        if (!empty($data['slug'])) {
            if ($query) {
                $query .= ' AND ';
            }
            $query .= 'slug = :slug:';
            $binds = ['slug' => $data['slug']];
        }

        if (empty($query)) {
            return null;
        }

        $query .= ' AND status="active" AND deleted_at IS NULL';

        return Model::findFirst([
            'conditions' => $query,
            'bind' => $binds,
            'order' => 'sort ASC'
        ]);
    }
}
