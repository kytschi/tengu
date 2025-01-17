<?php

/**
 * Queue traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Queue
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
