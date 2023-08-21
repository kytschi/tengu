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
                if (empty($tag) || empty($tag->value)) {
                    continue;
                }
                $model = Model::findFirst([
                    'conditions' => 'resource_id=:resource_id: AND tag=:tag: AND `type`=:type:',
                    'bind' => [
                        'resource_id' => $resource_id,
                        'tag' => $tag->value,
                        'type' => 'meta_keywords'
                    ]
                ]);

                if (empty($model)) {
                    $model = new Model([
                        'resource' => $resource,
                        'resource_id' => $resource_id,
                        'tag' => $tag->value,
                        'type' => 'meta_keywords'
                    ]);

                    if ($model->save() === false) {
                        throw new SaveException(
                            'Failed to add the tag',
                            $model->getMessages()
                        );
                    }
                } else {
                    $model->deleted_at = null;
                    $model->deleted_by = null;

                    if ($model->update() === false) {
                        throw new SaveException(
                            'Failed to update the tag',
                            $model->getMessages()
                        );
                    }
                }
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
            if (empty($tag) || empty($tag->value)) {
                continue;
            }
            $model = Model::findFirst([
                'conditions' => 'resource_id=:resource_id: AND tag=:tag: AND type IS NULL',
                'bind' => [
                    'resource_id' => $resource_id,
                    'tag' => $tag->value
                ]
            ]);

            if (empty($model)) {
                $model = new Model([
                    'resource' => $resource,
                    'resource_id' => $resource_id,
                    'tag' => $tag->value
                ]);

                if ($model->save() === false) {
                    throw new SaveException(
                        'Failed to add the tag',
                        $model->getMessages()
                    );
                }
            } else {
                $model->deleted_at = null;
                $model->deleted_by = null;

                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the tag',
                        $model->getMessages()
                    );
                }
            }
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
