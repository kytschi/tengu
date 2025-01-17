<?php

/**
 * Website routes.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
    UrlHelper::backend($config->urls->cms . '/blog-posts/categories/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'BlogPostCategories',
        'action'     => 'recover',
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
 * Menu categories
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/categories'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'MenuCategories',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/categories/add'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'MenuCategories',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/categories/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'MenuCategories',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/categories/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'MenuCategories',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/categories/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'MenuCategories',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/categories/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'MenuCategories',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/menu/categories/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'MenuCategories',
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
    UrlHelper::backend($config->urls->cms . '/pages/categories/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PageCategories',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/categories/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PageCategories',
        'action'     => 'save'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/pages/categories/update/{id}'),
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
 * Portfolio Categories
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/categories'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PortfolioCategories',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/categories/add'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PortfolioCategories',
        'action'     => 'add'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/categories/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PortfolioCategories',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/categories/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PortfolioCategories',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/categories/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PortfolioCategories',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/categories/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PortfolioCategories',
        'action'     => 'save'
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/portfolio/categories/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'PortfolioCategories',
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
 * Server logs.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/server-logs'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'ServerLogs',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/server-logs/add'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'ServerLogs',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/server-logs/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'ServerLogs',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/server-logs/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'ServerLogs',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/server-logs/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'ServerLogs',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/server-logs/save'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'ServerLogs',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/server-logs/update/{id}'),
    [
        'namespace'  => 'Kytschi\Tengu\Controllers\Website',
        'controller' => 'ServerLogs',
        'action'     => 'update',
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
