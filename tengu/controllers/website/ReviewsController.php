<?php

/**
 * Reviews controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\ReviewsController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Website;

use Kytschi\Makabe\Controllers\PersonasController;
use Kytschi\Tengu\Controllers\Website\PageCategoriesController;
use Kytschi\Tengu\Controllers\Website\PagesController;
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

class ReviewsController extends PagesController
{
    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/reviews';
    public $resource = 'review';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    public function addAction($template = 'website/reviews/add')
    {
        $this->setPageTitle('Build a review');
        return parent::addAction($template);
    }

    public function editAction($id, $template = 'website/reviews/edit')
    {
        $this->setPageTitle('Managing the review');
        return parent::editAction($id, $template);
    }

    public function indexAction($template = 'website/reviews/index')
    {
        $this->setPageTitle('Reviews');
        return parent::indexAction($template);
    }
}
