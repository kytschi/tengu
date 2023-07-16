<?php

/**
 * Projects controller.
 *
 * @package     Kytschi\Mai\Controllers\ProjectsController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Mai\Controllers;

use Kytschi\Tengu\Controllers\Core\ProjectsController as ControllerBase;

class ProjectsController extends ControllerBase
{
    public $dir = 'mai/projects';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->hrs . $this->global_url;
    }
}
