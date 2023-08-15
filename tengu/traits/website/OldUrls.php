<?php

/**
 * Old Urls traits.
 *
 * @package     Kytschi\Tengu\Traits\Website\OldUrls
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

namespace Kytschi\Tengu\Traits\Website;

use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Models\Website\OldUrls as Model;
use Kytschi\Tengu\Traits\Core\User;
use Phalcon\Encryption\Security\Random;

trait OldUrls
{
    use User;

    public function addOldUrl($resource_id)
    {
        if (!empty($_POST['old_url'])) {
            $model = new Model([
                'resource' => $this->resource,
                'resource_id' => $resource_id,
                'url' => $_POST['old_url']
            ]);

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to add the old URL',
                    $model->getMessages()
                );
            }

            return;
        }

        $this
            ->modelsManager
            ->executeQuery(
                'UPDATE ' .
                Model::class .
                ' SET deleted_at=NOW(), deleted_by=:deleted_by: WHERE resource_id = :resource_id:',
                [
                    'deleted_by' => self::getUserId(),
                    'resource_id' => $resource_id
                ]
            );

        if (empty($_POST['old_urls'])) {
            return;
        }

        if ($urls = $_POST['old_urls']) {
            foreach ($urls as $key => $url) {
                if (empty($url)) {
                    continue;
                }

                $this->db->query(
                    'INSERT INTO
                    old_urls 
                        (id, resource, resource_id, url, created_at, created_by, updated_at, updated_by)
                    SELECT
                        :id,
                        :resource,
                        :resource_id,
                        :url,
                        :created_at,
                        :created_by,
                        :updated_at,
                        :updated_by
                    FROM DUAL
                    WHERE NOT EXISTS
                    (
                        SELECT
                            id,
                            resource,
                            resource_id,
                            url,
                            created_at,
                            created_by,
                            updated_at,
                            updated_by
                        FROM old_urls
                        WHERE
                            resource_id=:resource_id_2 AND url=:url_2
                    )',
                    [
                        ':resource' => $this->resource,
                        ':resource_id' => $resource_id,
                        ':resource_id_2' => $resource_id,
                        ':url' => $url,
                        ':url_2' => $url,
                        ':id' => (new Random())->uuid(),
                        ':created_at' => date('Y-m-d H:i:s'),
                        ':created_by' => self::getUserId(),
                        ':updated_at' => date('Y-m-d H:i:s'),
                        ':updated_by' => self::getUserId()
                    ]
                );

                $this->db->query(
                    'UPDATE old_urls 
                        SET deleted_at=NULL, deleted_by=NULL, status=:status
                        WHERE resource_id=:resource_id AND url=:url',
                    [
                        ':resource_id' => $resource_id,
                        ':url' => $url,
                        ':status' => !empty($_POST['old_urls_status'][$key]) ?
                            'active' :
                            'inactive'
                    ]
                );
            }
        }
    }

    public function checkOldUrl($url)
    {
        $found = Model::findFirst([
            'conditions' => 'deleted_at IS NULL AND (url = :url: OR url = :url_slash:) AND status = "active"',
            'bind' => [
                'url' => $url,
                'url_slash' => rtrim($url, '/') . '/'
            ]
        ]);

        if ($found) {
            return $found->page;
        }

        return null;
    }
}
