<?php

/**
 * Portfolio Categories controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\PortfolioCategoriesController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Website;

use Kytschi\Tengu\Controllers\Website\PageCategoriesController;

class PortfolioCategoriesController extends PageCategoriesController
{
    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/portfolio/categories';
    public $global_from_url = '/portfolio';
    public $resource = 'portfolio-category';
    public $category_support = false;

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
        $this->global_from_url = ($this->di->getConfig())->urls->cms . $this->global_from_url;
        $this->global_add_url = $this->global_url . '/add';
        $this->global_category_url = $this->global_url;
    }

    public function addAction($title = 'Adding a portfolio category', $template = 'website/pages/add')
    {
        return parent::addAction($title, $template);
    }

    public function editAction($id, $title = 'Managing the portfolio category', $template = 'website/pages/edit')
    {
        return parent::editAction($id, $title, $template);
    }

    public function indexAction($title = 'Portfolio categories', $template = 'website/page_categories/index')
    {
        return parent::indexAction($title, $template);
    }
}
