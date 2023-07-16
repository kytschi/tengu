<?php

/**
 * Products controller.
 *
 * @package     Kytschi\Phoenix\Controllers\ProductCategoriesController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Makabe\Controllers\PersonasController;
use Kytschi\Phoenix\Models\Products;
use Kytschi\Tengu\Controllers\Website\PageCategoriesController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Website\Templates;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Website\OldUrls;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Queue;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Paginator\Adapter\QueryBuilder;

class ProductCategoriesController extends PageCategoriesController
{
    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/products/categories';
    public $resource = 'product-category';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->sales . $this->global_url;
    }

    public function addAction($template = 'true')
    {
        $this->setPageTitle('Adding a product category');
        return parent::addAction($template);
    }

    public function editAction($id, $template = 'true')
    {
        $this->setPageTitle('Managing the product category');
        return parent::editAction($id, $template);
    }

    public function indexAction($template = 'true')
    {
        $this->setPageTitle('Product categories');
        return parent::indexAction($template);
    }

    public function saveAction($ignore = true)
    {
        parent::saveAction($ignore);
    }
}
