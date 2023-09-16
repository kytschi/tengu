<?php

/**
 * Notes traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Notes
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
