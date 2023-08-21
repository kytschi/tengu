<?php

/**
 * Queue traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Queue
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

namespace Kytschi\Tengu\Traits\Core;

use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Models\Core\Queue as Model;

trait Queue
{
    public function addJobToQueue($resource, $resource_id, $job, $priority = 'normal')
    {
        if ($priority == 'normal') {
            $priority_code = 1;
        } elseif ($priority == 'low') {
            $priority_code = 2;
        } elseif ($priority == 'high') {
            $priority_code = 0;
        }

        $model = new Model([
            'resource_id' => $resource_id,
            'resource' => $resource,
            'job' => $job,
            'priority' => $priority_code
        ]);

        if ($model->save() === false) {
            throw new SaveException(
                'Failed to add the job',
                $model->getMessages()
            );
        }
    }
}
