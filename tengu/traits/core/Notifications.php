<?php

/**
 * Notifications traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Notifications
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
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
