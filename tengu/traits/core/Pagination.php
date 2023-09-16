<?php

/**
 * Pagination traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Pagination
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Traits\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Traits\Core\Security;

trait Pagination
{
    use Security;

    public $page = 1;
    public $perPage = 30;
    public $orderBy = '';
    public $orderDir = '';

    public static function getPagination($var)
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return '';
        }

        return $controller->session->get($var);
    }

    public function savePagination()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }

        $controller->session->set('page', !empty($_GET['page']) ? intval($_GET['page']) : 1);
        $this->page = !empty($_GET['page']) ? intval($_GET['page']) : 1;

        $controller->session->set('limit', !empty($_GET['limit']) ? intval($_GET['limit']) : 30);
        $this->perPage = !empty($_GET['limit']) ? intval($_GET['limit']) : 30;

        if (!empty($_GET['filters'])) {
            $controller->session->set('filters', self::cleanString($_GET['filters']));
        } else {
            $controller->session->remove('filters');
        }

        if (!empty($_GET['order_by'])) {
            $controller->session->set('order_by', self::cleanString($_GET['order_by']));
        } else {
            $controller->session->remove('order_by');
        }

        if (!empty($_GET['order_dir'])) {
            $controller->session->set('order_dir', self::cleanString($_GET['order_dir']));
        } else {
            $controller->session->remove('order_dir');
        }

        if (!empty($_GET['search'])) {
            $controller->session->set(
                'search',
                self::cleanString($_GET['search'])
            );
        } else {
            $controller->session->remove('search');
        }
    }

    public function setIndexDefaults(array $valid_order_bys, string $default_order_by)
    {
        $this->orderBy = $default_order_by;
        if (empty($this->orderDir)) {
            $this->orderDir = 'ASC';
        }

        if (!empty($_GET['filters'])) {
            if (!empty($_GET['filters']['order_by'])) {
                $splits = explode('|', $_GET['filters']['order_by']);
                $this->orderBy = $splits[0];

                if (isset($splits[1])) {
                    $this->orderDir = strtoupper($splits[1]);
                }
            }
        }

        if (!in_array($this->orderBy, $valid_order_bys)) {
            $this->orderBy = $default_order_by;
        }

        if (!in_array(strtoupper($this->orderDir), ['ASC', 'DESC'])) {
            $this->orderDir = 'ASC';
        }

        $this->perPage = !empty($_GET['limit']) ? intval($_GET['limit']) : 30;
        $this->page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
        $this->search = !empty($_GET['search']) ? urldecode($_GET['search']) : '';
    }
}
