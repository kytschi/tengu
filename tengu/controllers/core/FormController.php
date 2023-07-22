<?php

/**
 * Form controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\FormController
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

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Tags;

class FormController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Tags;

    public function addAjaxImageAction()
    {
        $this->secure();
        $this->addAjaxImage();
    }

    public static function booleanConvert($value)
    {
        if ($value == 'on' || $value == '1') {
            return true;
        }

        return false;
    }

    public static function getCountries()
    {
        return [
            'United Kingdom'
        ];
    }
}
