<?php

/**
 * Pages traits.
 *
 * @package     Kytschi\Tengu\Traits\Website\Pages
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Traits\Website;

use Kytschi\Tengu\Controllers\Website\BlogPostsController;
use Kytschi\Tengu\Models\Website\Pages as Model;

trait Pages
{
    public function findFeaturedPages($count = 3, $exclude = null, $random = false)
    {
        return (new BlogPostsController())->get($count, $exclude, $random, true);
    }

    public function findRandomPages($count = 3, $exclude = null)
    {
        return (new BlogPostsController())->get($count, $exclude, true);
    }

    public function findSimilarPages($count = 3, $exclude = null)
    {
        return (new BlogPostsController())->get($count, $exclude, false);
    }

    public function findPage($data)
    {
        if (empty($data)) {
            return null;
        }

        if (empty($data['id'])) {
            return null;
        }

        $binds = ['id' => $data['id']];

        return Model::findFirst([
            'conditions' => 'id = :id:',
            'bind' => $binds
        ]);
    }

    public function findPageByTag($data)
    {
        if (empty($data)) {
            return null;
        }

        if (empty($data['tag'])) {
            return null;
        }

        $binds = ['search_tags' => '%' . $data['tag'] . ',%'];

        return Model::findFirst([
            'conditions' => 'search_tags LIKE :search_tags:',
            'bind' => $binds
        ]);
    }

    public function findPagesByTag($data)
    {
        if (empty($data)) {
            return null;
        }

        if (empty($data['tag'])) {
            return null;
        }

        $binds = ['search_tags' => '%' . $data['tag'] . ',%'];

        return Model::find([
            'conditions' => 'search_tags LIKE :search_tags:',
            'bind' => $binds
        ]);
    }
}
