<?php

/**
 * Tags traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Tags
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
use Kytschi\Tengu\Models\Core\Tags as Model;
use Kytschi\Tengu\Traits\Core\User;
use Phalcon\Encryption\Security\Random;

trait Tags
{
    use User;

    public function addMetaKeywords($resource, $resource_id, $user_id, bool $clear_old = false)
    {
        if ($clear_old) {
            $this
                ->modelsManager
                ->executeQuery(
                    'UPDATE ' .
                    Model::class .
                    '   SET deleted_at=NOW(), deleted_by=:deleted_by: 
                        WHERE resource_id = :resource_id: AND type="meta_keywords"',
                    [
                        'deleted_by' => $user_id,
                        'resource_id' => $resource_id
                    ]
                );
        }

        if (empty($_POST['meta_keywords'])) {
            return;
        }

        if ($tags = json_decode($_POST['meta_keywords'])) {
            foreach ($tags as $tag) {
                $this->db->query(
                    'INSERT INTO
                        tags (
                            id,
                            `resource`,
                            resource_id,
                            tag,
                            created_at,
                            created_by,
                            updated_at,
                            updated_by, 
                            `type`
                        )
                        SELECT
                            :id,
                            :resource,
                            :resource_id,
                            :tag,
                            :created_at,
                            :created_by,
                            :updated_at,
                            :updated_by,
                            :type
                        FROM DUAL
                        WHERE NOT EXISTS
                        (
                            SELECT
                                id,
                                resource,
                                resource_id,
                                tag,
                                created_at,
                                created_by,
                                updated_at,
                                updated_by,
                                type
                            FROM tags
                            WHERE
                                resource_id=:resource_id AND tag=:tag AND `type`=:type
                        );
                    ',
                    [
                        ':resource' => $resource,
                        ':resource_id' => $resource_id,
                        ':tag' => $tag->value,
                        ':id' => (new Random())->uuid(),
                        ':created_at' => date('Y-m-d H:i:s'),
                        ':created_by' => $user_id,
                        ':updated_at' => date('Y-m-d H:i:s'),
                        ':updated_by' => $user_id,
                        ':type' => 'meta_keywords',
                    ]);
                $this->db->query('UPDATE tags 
                        SET deleted_at=NULL, deleted_by=NULL 
                        WHERE resource_id=:resource_id AND tag=:tag AND `type`=:type',
                    [
                        ':resource_id' => $resource_id,
                        ':tag' => $tag->value,
                        ':type' => 'meta_keywords',
                    ]
                );
            }
        }
    }

    private function addSearchTagsMake($tags)
    {
        $string = '';
        foreach ($tags as $tag) {
            if (!empty($tag->tag)) {
                $value = $tag->tag;
            } else {
                $value = $tag->value;
            }
            $string .= str_replace([':', ',', '.', '|'], '', strtolower($value)) . ' ';
        }
        $words = explode(' ', rtrim($string));
        $tmp = [];
        foreach ($words as $word) {
            $tmp[$word] = $word;
        }
        return implode(' ', $tmp);
    }

    public function addSearchTags($model)
    {
        $string = '';
        if (!empty($_POST['tags'])) {
            if ($tags = json_decode($_POST['tags'])) {
                $string = $this->addSearchTagsMake($tags);
            }
        } elseif ($model->tags->count()) {
            $string = $this->addSearchTagsMake($model->tags);
        }
        $model->search_tags = $string;
        return $model;
    }

    public function addTags($tags, $resource_id = null, bool $clear_old = false, $resource = null)
    {
        if (empty($resource)) {
            $resource = $this->resource;
        }

        if (empty($resource_id)) {
            $resource_id = $this->resource_id;
        }

        $user_id = self::getUserId();

        if ($clear_old) {
            $this
                ->modelsManager
                ->executeQuery(
                    'UPDATE ' .
                    Model::class .
                    ' SET 
                        deleted_at=NOW(),
                        deleted_by=:deleted_by: 
                    WHERE 
                        resource_id = :resource_id: AND 
                        type IS NULL',
                    [
                        'deleted_by' => $user_id,
                        'resource_id' => $resource_id
                    ]
                );
        }
        
        foreach ($tags as $tag) {
            $this->db->query(
                'INSERT INTO
                    tags (id, `resource`, resource_id, tag, created_at, created_by, updated_at, updated_by)
                    SELECT
                        :id,
                        :resource,
                        :resource_id,
                        :tag,
                        :created_at,
                        :created_by,
                        :updated_at,
                        :updated_by
                    FROM DUAL
                    WHERE NOT EXISTS
                    (
                        SELECT
                            id,
                            `resource`,
                            resource_id,
                            tag,
                            created_at,
                            created_by,
                            updated_at,
                            updated_by
                        FROM tags
                        WHERE
                            resource_id=:resource_id_2 AND tag=:tag_2 AND `type` IS NULL
                    )',
                    [
                        ':resource' => $resource,
                        ':resource_id' => $resource_id,
                        ':resource_id_2' => $resource_id,
                        ':tag' => $tag->value,
                        ':tag_2' => $tag->value,
                        ':id' => (new Random())->uuid(),
                        ':created_at' => date('Y-m-d H:i:s'),
                        ':created_by' => $user_id,
                        ':updated_at' => date('Y-m-d H:i:s'),
                        ':updated_by' => $user_id,
                    ]);
            $this->db->query('UPDATE tags 
                    SET deleted_at=NULL, deleted_by=NULL 
                    WHERE resource_id=:resource_id AND tag=:tag AND `type` IS NULL',
                [
                    ':resource_id' => $resource_id,
                    ':tag' => $tag->value
                ]
            );
        }
    }

    public function addTagsFromRequest($resource_id, bool $clear_old = false, $resource = null)
    {
        if (!empty($_POST['tags'])) {
            $tags = json_decode($_POST['tags']);
        } else {
            $tags = [];
        }

        $this->addTags($tags, $resource_id, $clear_old, $resource);
    }

    public function findFileByTag($tag)
    {
        return Model::findFirst(
            [
                'conditions' => 'tag=:tag: AND deleted_at IS NULL AND resource="file"',
                'bind' => [
                    'tag' => $tag
                ]
            ]
        );
    }

    public function findFilesByTag($tag)
    {
        return Model::find(
            [
                'conditions' => 'tag=:tag: AND deleted_at IS NULL AND resource="file"',
                'bind' => [
                    'tag' => $tag
                ]
            ]
        );
    }

    public function searchTags($tags)
    {
        $return = ',';
        $tags = json_decode($tags);

        foreach ($tags as $tag) {
            $return .= $tag->value . ',';
        }

        return $return;
    }

    public function tagsToString($tags)
    {
        if (is_string($tags)) {
            if (substr(trim($tags), 0, 2) == '[{') {
                return str_replace('"}]', ',', str_replace('[{"value":"', '', trim($tags)));
            } else {
                return $tags;
            }
        }

        $string = '';
        foreach ($tags as $tag) {
            $string .= $tag->tag . ', ';
        }
        return rtrim($string, ', ');
    }
}
