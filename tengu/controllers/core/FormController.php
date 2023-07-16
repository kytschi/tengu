<?php

/**
 * Form controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\FormController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
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
