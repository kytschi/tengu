<?php

/**
 * Notifications traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Notifications
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
use Kytschi\Tengu\Models\Core\Notifications as Model;
use Kytschi\Tengu\Traits\Core\User;

trait Notifications
{
    use User;

    public function notify(
        string $to_user_id,
        string $resource,
        string $resource_id,
        string $type,
        string $subject,
        $content = null,
        string $url = null,
        $check = false
    ) {
        if ($check) {
            if (
                $model = Model::findFirst([
                    'conditions' => 'to_user_id = :to_user_id: AND resource_id = :resource_id: AND deleted_at IS NULL',
                    'bind' => [
                        'to_user_id' => $to_user_id,
                        'resource_id' => $resource_id
                    ]
                ])
            ) {
                return;
            }
        }

        if (empty($content)) {
            $content = $subject;
        }

        $model = new Model([
            'to_user_id' => $to_user_id,
            'resource_id' => $resource_id,
            'resource' => $resource,
            'type' => $type,
            'subject' => $subject,
            'content' => $content,
            'from_user_id' => self::getUserId(),
            'url' => $url
        ]);
        
        if ($model->save() === false) {
            throw new SaveException(
                'Failed to add the notification',
                $model->getMessages()
            );
        }
    }
}
