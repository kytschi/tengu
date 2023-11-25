<?php

/**
 * Page Categories traits.
 *
 * @package     Kytschi\Tengu\Traits\Website\PageCategories
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Traits\Website;

use Kytschi\Izumi\Models\Events;
use Kytschi\Tengu\Controllers\Core\PostcodesController;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Website\PageCategories as Model;
use Phalcon\Paginator\Adapter\QueryBuilder;

trait PageCategories
{
    public function findCategory($data)
    {
        if (empty($data)) {
            return null;
        }

        if (empty($data['id'])) {
            return null;
        }

        $binds = ['id' => $data['id']];

        return Pages::find([
            'conditions' => 'id = :id:',
            'bind' => $binds
        ]);
    }

    public function findCategoryItems($data)
    {
        if (empty($data)) {
            return null;
        }

        if (empty($data['category_id'])) {
            return null;
        }

        $binds = ['category_id' => $data['category_id']];

        $model = new Pages();
        $pages_table = $model->getSource();
        $cats_table = (new Model())->getSource();
        $events_table = (new Events())->getSource();

        $selects = "$pages_table.*";

        $wheres = "$pages_table.status='active'";
        if (!empty($data['where'])) {
            $wheres = ' AND ' . $data['where'];
        }

        if (!empty($data['data'])) {
            $binds = array_merge($binds, $data['data']);
        }

        $order = "categories.sort ASC, categories.created_at DESC";
        if (!empty($data['order'])) {
            $order = 'ORDER BY ' . $data['order'];
        }

        if (!empty($data['search'])) {
            $controller = new PostcodesController();
            if ($postcodes = $controller->hasPostcode($data['search'])) {
                if (!empty($data['radius'])) {
                    $postcode = reset($postcodes);
                    $coords = $controller->getCoordinates($postcode);
                    $latitude = $coords['latitude'];
                    $longitude = $coords['longitude'];
                    $selects .= ", 69.0 *
                        DEGREES(ACOS(LEAST(1.0, COS(RADIANS($latitude))
                                * COS(RADIANS(latitude))
                                * COS(RADIANS($longitude - longitude))
                                + SIN(RADIANS($latitude))
                                * SIN(RADIANS(latitude))))) AS distance ";

                    $order = ' HAVING distance < ' . $data['radius'] . ' ORDER BY distance ASC';
                } else {
                    $wheres .= ' AND (name LIKE :name OR location LIKE :location OR ';
                    $binds['name'] = '%' . $data['search'] . '%';
                    $binds['location'] = '%' . $data['search'] . '%';
                    foreach ($postcodes as $key => $postcode) {
                        $wheres .= "$pages_table.postcode = :postcode_' . $key . '_1 OR 
                            $pages_table.postcode = :postcode_' . $key . '_2 OR
                            $pages_table.postcode LIKE :postcode_' . $key . '_3 OR
                            $pages_table.postcode LIKE :postcode_' . $key . '_4 OR ";
                        $binds['postcode_' . $key . '_1'] = $postcode;
                        $binds['postcode_' . $key . '_2'] = str_replace(' ', '', $postcode);
                        $binds['postcode_' . $key . '_3'] = '%' . $postcode . '%';
                        $binds['postcode_' . $key . '_4'] = '%' . str_replace(' ', '', $postcode) . '%';
                    }
                    $wheres = rtrim($wheres, ' OR ') . ')';
                }
            } else {
                $wheres .= ' AND (name LIKE :name OR location LIKE :location OR ';
                $wheres .= "$pages_table.postcode LIKE :postcode_1 OR $pages_table.postcode LIKE :postcode_2)";
                $binds['postcode_1'] = '%' . $data['search'] . '%';
                $binds['postcode_2'] = '%' . $data['search'] . '%';
                $binds['name'] = '%' . $data['search'] . '%';
                $binds['location'] = '%' . $data['search'] . '%';
                $order = "ORDER BY " . $order;
            }
        }

        if (!empty($data['pagination'])) {
            $limit = 30;
            if (!empty($data['pagination']['limit'])) {
                $limit = intval($data['pagination']['limit']);
            }

            $page = 1;
            if (!empty($data['pagination']['page'])) {
                $page = intval($data['pagination']['page']);
            }

            /*if ($wheres) {
                $wheres = str_replace('pages.', Pages::class . '.', $wheres);
                $wheres = str_replace('categogies.', Model::class . '.', $wheres);
                $wheres = str_replace('events.', Events::class . '.', $wheres);
            }

            if ($selects) {
                $selects = str_replace('pages.', Pages::class . '.', $selects);
                $selects = str_replace('categogies.', Model::class . '.', $selects);
                $selects = str_replace('events.', Events::class . '.', $selects);
            }*/

            /*if ($order) {
                $order = str_replace('pages.', '[' . Pages::class . '].', $order);
                $order = str_replace('categogies.', '[' . Model::class . '].', $order);
                $order = str_replace('events.', '[' . Events::class . '].', $order);
            }*/

            // Just need a controller for the modelsManager
            $builder = (new PostcodesController())
                ->modelsManager
                ->createBuilder()
                ->columns($selects)
                ->addFrom(Model::class, 'categories')
                ->join(Pages::class, 'pages.id=categories.page_id AND pages.deleted_at IS NULL', 'pages')
                ->where("categories.category_id = :category_id: AND 
                    categories.deleted_at IS NULL AND
                    pages.id IS NOT NULL")
                ->andWhere(ltrim($wheres, ' AND '))
                ->orderBy(ltrim($order, "ORDER BY "));

            $builder->setBindParams($binds);
            $paginator = new QueryBuilder(
                [
                    "builder" => $builder,
                    "limit" => !empty($limit) ? $limit : 30,
                    "page" => !empty($page) ? $page : 1,
                ]
            );

            return $paginator->paginate();
        }

        $query = "SELECT $selects
        FROM  $cats_table AS categories
        LEFT JOIN $pages_table ON $pages_table.id = categories.page_id AND $pages_table.deleted_at IS NULL
        LEFT JOIN $events_table ON $events_table.id = categories.page_id AND $events_table.deleted_at IS NULL
        WHERE 
            category_id = :category_id AND 
            categories.deleted_at IS NULL AND 
            $pages_table.id IS NOT NULL AND 
            $wheres
        $order";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model
                ->getReadConnection()
                ->query(
                    rtrim($query, ','),
                    $binds
                )
        ));
    }
}
