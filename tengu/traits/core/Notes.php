<?php

/**
 * Notes traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Notes
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
use Kytschi\Tengu\Models\Core\Notes as Model;
use Kytschi\Tengu\Traits\Core\Logs;

trait Notes
{
    use Logs;

    public function addNote(string $note, $resource = null, $resource_id = null)
    {
        if (empty($note)) {
            return;
        }

        if (empty($resource)) {
            $resource = $this->resource;
        }

        if (empty($resource_id)) {
            $resource_id = $this->resource_id;
        }

        $model = new Model([
            'content' => $note,
            'resource' => $resource,
            'resource_id' => $resource_id
        ]);

        if ($model->save() === false) {
            throw new SaveException(
                'Failed to save the note',
                $model->getMessages()
            );
        }

        $this->addLog(
            $resource,
            $resource_id,
            'info',
            'Note added by ' . $this->getUserFullName()
        );
    }

    public function addNoteFromRequest($resource_id = null, $resource = null, string $key = 'add_note')
    {
        if (empty($_REQUEST[$key])) {
            return;
        }

        $this->addNote($_REQUEST[$key], $resource, $resource_id);
    }
}
