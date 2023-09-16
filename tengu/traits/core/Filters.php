<?php

/**
 * Filters traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Filters
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Traits\Core;

use Kytschi\Tengu\Controllers\ControllerBase;

trait Filters
{
    public function getFilters($key = '', $default = '')
    {
        $this->setFilters();
        
        if (empty($this->filters)) {
            return null;
        }
                
        if (is_string($key) && !empty($this->filters)) {
            return !empty($this->filters[$key]) ? $this->filters[$key] : $default;
        }

        return $this->filters;
    }

    public function setFilters()
    {
        if (!empty($_GET['filters'])) {
            if (is_array($_GET['filters'])) {
                $this->filters = $_GET['filters'];
            } else {
                $splits = explode('_', $_GET['filters']);
                $label = $splits[0];
                unset($splits[0]);
                $this->filters[$label] = implode(' ', $splits);
            }
        }
    }
}
