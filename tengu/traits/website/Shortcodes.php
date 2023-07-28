<?php

/**
 * Shortcodes traits.
 *
 * @package     Kytschi\Tengu\Traits\Website\Shortcodes
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

use Kytschi\Tengu\Traits\Core\Tags;

trait Shortcodes
{
    use Tags;

    public function parseShortcodes($content)
    {
        if (empty($content)) {
            return $content;
        }

        preg_match_all('/%(.*?)%/si', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $key => $match) {
                if (strtolower(substr($match, 0, strlen('imgbytag'))) == 'imgbytag') {
                    if ($file = $this->findFileByTag(str_replace(['imgbytag(\'', '\')'], '', strtolower($match)))) {
                        $content = str_replace(
                            $matches[0][$key],
                            '<img src="' . $file->file->url .
                            '" title="' . $file->file->label .
                            '" alt="' . $file->file->label . '">',
                            $content
                        );
                    } else {
                        $content = str_replace($matches[0][$key], '', $content);
                    }
                }
            }
        }
        return $content;
    }
}
