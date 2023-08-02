<?php

/**
 * Menu categories controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\MenuCategoriesController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301, USA.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Website;

use Kytschi\Tengu\Controllers\Website\MenuController;
use Kytschi\Tengu\Models\Website\MenuCategories;
use Phalcon\Encryption\Security\Random;

class MenuCategoriesController extends MenuController
{
    public $global_url = '/menu/categories';
    public $resource = 'menu-category';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    /**
     * Add action
     *
     * @param string $title The window title
     * @return HTML
     */
    public function addAction($title = 'Add a menu category')
    {
        parent::addAction($title);
    }

    /**
     * Add the categories
     *
     * @param string $page_id The page ID
     * @return void
     */
    public function addCategories($menu_id)
    {
        $user_id = self::getUserId();

        $this
            ->modelsManager
            ->executeQuery(
                'UPDATE ' .
                MenuCategories::class .
                ' SET deleted_at=NOW(), deleted_by=:deleted_by: WHERE menu_id = :menu_id:',
                [
                    'deleted_by' => $user_id,
                    'menu_id' => $menu_id
                ]
            );

        if (!empty($_POST['category_id'])) {
            $table = (new MenuCategories())->getSource();

            foreach ($_POST['category_id'] as $key => $id) {
                if (empty($id)) {
                    continue;
                }

                $this->db->query(
                    'INSERT INTO ' . $table . ' 
                    (id, category_id, menu_id, created_at, created_by, updated_at, updated_by)
                    SELECT
                        :id_' . $key . ',
                        :category_id_' . $key . ',
                        :menu_id,
                        :created_at,
                        :created_by,
                        :updated_at,
                        :updated_by
                    FROM DUAL WHERE NOT EXISTS
                    (
                        SELECT
                            id,
                            menu_id,
                            category_id,
                            created_at,
                            created_by,
                            updated_at,
                            updated_by
                        FROM ' . $table . '
                        WHERE menu_id=:menu_id_2 AND category_id=:category_id_' . $key . '_2 
                    )',
                    [
                        ':id_' . $key => (new Random())->uuid(),
                        ':category_id_' . $key => $id,
                        ':category_id_' . $key . '_2' => $id,
                        ':menu_id' => $menu_id,
                        ':menu_id_2' => $menu_id,
                        ':created_at' => date('Y-m-d H:i:s'),
                        ':created_by' => $user_id,
                        ':updated_at' => date('Y-m-d H:i:s'),
                        ':updated_by' => $user_id
                    ]
                );
                $this->db->query(
                    'UPDATE ' . $table . '
                    SET deleted_at=NULL, deleted_by=NULL 
                    WHERE menu_id=:menu_id AND category_id=:category_id_' . $key,
                    [
                        ':category_id_' . $key => $id,
                        ':menu_id' => $menu_id
                    ]
                );
            }
        }

        if (!empty($_POST['category_sort'])) {
            $iLoop = 0;
            foreach ($_POST['category_sort'] as $id => $sort) {
                if (empty($id)) {
                    continue;
                }

                $this->db->query(
                    'UPDATE ' . $table . '
                    SET sort=:sort 
                    WHERE menu_id=:menu_id_' . $iLoop . ' AND category_id=:category_id',
                    [
                        ':category_id' => $menu_id,
                        ':menu_id_' . $iLoop => $id,
                        ':sort' => $sort
                    ]
                );
                $iLoop++;
            }
        }
    }

    /**
     * Edit action
     *
     * @param string $id The id of the entry
     * @param string $title The page title
     * @return HTML
     */
    public function editAction($id, $title = 'Edit the menu category')
    {
        parent::editAction($id, $title);
    }

    /**
     * Index action
     *
     * @param $title The window title
     * @param $template The template to be used
     * @return HTML
     */
    public function indexAction($title = 'Menu categories', $template = 'website/menu/categories/index')
    {
        parent::indexAction($title, $template);
    }
}