<?php

/**
 * Search trait.
 *
 * @package     Kytschi\Tengu\Traits\Website\Search
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Traits\Website;

use Kytschi\Tengu\Models\Website\SearchStats;

trait Search
{
    public function findTrendingSearch($count = 10)
    {
        $query = "SELECT 
            query,
            count(query) AS total
        FROM search_stats
        WHERE deleted_at IS NULL 
        GROUP BY query
        ORDER BY total DESC
        LIMIT :count";

        $model = new SearchStats();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(
                rtrim($query, ','),
                ['count' => $count]
            )
        ));
    }
}
