<?php

/**
 * Restock controller.
 *
 * @package     Kytschi\Phoenix\Controllers\RestockController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Phoenix\Models\Products;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class RestockController extends ControllerBase
{
    use Form;
    use Logs;
    use Notes;
    use Pagination;
    use Tags;

    public $access = [
        'super-user'
    ];

    public $global_url = '/products/restock';
    public $resource = 'product';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->sales . $this->global_url;
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Products restock');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'name',
                'url',
                'created_by',
                'status'
            ],
            'name'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Pages::class)
            ->where('type = "' . $this->resource . '"')
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'name' => '%' . $this->search . '%',
                'url' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('name LIKE :name: OR url LIKE :url:');
        }

        if (!empty($this->filters)) {
            $iLoop = 1;
            foreach ($this->filters as $filter => $value) {
                if (empty($value)) {
                    continue;
                }

                switch ($filter) {
                    case 'status':
                        $builder->andWhere('status LIKE :status_' . $iLoop . ':');
                        $params['status_' . $iLoop] = $value;
                        $iLoop++;
                        break;
                }
            }
        }

        $builder->setBindParams($params);

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        return $this->view->partial(
            'phoenix/products/restock/index',
            [
                'data' => $paginator->paginate()
            ]
        );
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $url = $this->global_url;
        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        try {
            if (empty($_POST['quantity'])) {
                throw new SaveException('Nothing to update');
            }

            $table = (new Products())->getSource();
            $query = '';
            $params = [];
            
            $iLoop = 0;
            
            foreach ($_POST['quantity'] as $id => $stock) {
                $query .= 'UPDATE ' . $table . ' SET stock=:stock_' . $iLoop . ' WHERE id=:id_' . $iLoop . ';';
                $params[':stock_' . $iLoop] = $stock;
                $params[':id_' . $iLoop] = $id;
                $iLoop++;
            }

            $this->db->query(
                $query,
                $params
            );

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Stock updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Stock has been updated');

            $this->clearFormData();
            $this->redirect(UrlHelper::backend($url));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }
}
