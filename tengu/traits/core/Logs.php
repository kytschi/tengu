<?php

/**
 * Logs traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Logs
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Traits\Core;

use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Models\Core\Logs as Model;

trait Logs
{
    public function addLog(
        string $resource,
        $resource_id,
        string $type,
        string $summary,
        $content = null
    ) {
        if (is_array($content)) {
            $content = implode(', ', $content);
        } elseif (is_null($content)) {
            $content = '';
        }

        $model = new Model(
            [
                'resource' => $resource,
                'resource_id' => $resource_id,
                'summary' => $summary,
                'content' => strip_tags($content),
                'type' => $type,
            ]
        );

        if ($model->save() === false) {
            if (strpos(get_class($this), 'Exception') === false) {
                throw new SaveException(
                    'Failed to save the log',
                    $model->getMessages()
                );
            }
        }
    }
}
