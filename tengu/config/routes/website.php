<?php

/**
 * Website routes.
 *
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

use Kytschi\Tengu\Helpers\UrlHelper;

/*
 * Blog post Categories
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/categories'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPostCategories',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/categories/add'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPostCategories',
        'action'     => 'add'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/categories/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPostCategories',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/categories/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPostCategories',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/categories/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPostCategories',
        'action'     => 'save'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/categories/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPostCategories',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Blog posts.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPosts',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/create'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPosts',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPosts',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPosts',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPosts',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPosts',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/blog-posts/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPosts',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Menu.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/menu'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Menu',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/add'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Menu',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Menu',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Menu',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Menu',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Menu',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/sort/{id}/{dir}/{sort}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Menu',
        'action'     => 'sort',
        'id' => 1,
        'dir' => 2,
        'sort' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Menu',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Pages.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/pages'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Pages',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/create'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Pages',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Pages',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Pages',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Pages',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Pages',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Pages',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Page Categories
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/categories'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PageCategories',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/categories/add'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PageCategories',
        'action'     => 'add'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/categories/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PageCategories',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/categories/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PageCategories',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/page-categories/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PageCategories',
        'action'     => 'save'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/page-categories/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PageCategories',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Portfolio.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Portfolio',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/create'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Portfolio',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Portfolio',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Portfolio',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Portfolio',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Portfolio',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Portfolio',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Reviews.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/reviews'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Reviews',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/reviews/create'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Reviews',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/reviews/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Reviews',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/reviews/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Reviews',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/reviews/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Reviews',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/reviews/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Reviews',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/reviews/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Reviews',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Search stats.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/search-stats'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'SearchStats',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/search-stats/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'SearchStats',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/search-stats/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'SearchStats',
        'action'     => 'edit',
        'id' => 1
    ]
);

/*
 * Settings.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/settings'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Settings',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/settings/update'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Settings',
        'action'     => 'update'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/settings/stats-exclude/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Settings',
        'action'     => 'delete',
        'id' => 1
    ]
);

/*
 * Templates.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/templates'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Templates',
        'action' => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/templates/build'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Templates',
        'action' => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/templates/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Templates',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/templates/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Templates',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/templates/files'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Templates',
        'action'     => 'files'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/templates/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Templates',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/templates/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Templates',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/templates/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Templates',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Themes.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/themes'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Themes',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/themes/add'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Themes',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/themes/active'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Themes',
        'action'     => 'setActive',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/themes/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Themes',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/themes/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Themes',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/themes/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Themes',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/themes/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Themes',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/themes/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'Themes',
        'action'     => 'update',
        'id' => 1
    ]
);
