<?php

/**
 * Keywords traits.
 *
 * @package     Kytschi\Makabe\Traits\Keywords
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

namespace Kytschi\Makabe\Traits;

use Kytschi\Makabe\Models\Keywords as Model;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Traits\Core\Notes;

trait Keywords
{
    use Notes;

    public function addKeyword($resource, $resource_id)
    {
        if (empty($_POST['keyword'])) {
            throw new ValidationException('Missing required keyword data');
        }

        if (empty($_POST['global'])) {
            $data = [
                'resource' => $resource,
                'resource_id' => $resource_id,
                'keyword' => $_POST['keyword']
            ];
        } else {
            $data = [
                'keyword' => $_POST['keyword']
            ];
        }

        $model = new Model($data);

        if ($model->save() === false) {
            throw new SaveException(
                'Failed to create the keyword entry',
                $model->getMessages()
            );
        }
    }

    public function addKeywordNotes($resource)
    {
        if (empty($_POST['keyword_note'])) {
            return;
        }

        foreach ($_POST['keyword_note'] as $id => $note) {
            if (empty($note)) {
                continue;
            }

            $this->addNote(
                $note,
                $resource,
                $id
            );
        }
    }
}
