<?php

/**
 * Shortcodes traits.
 *
 * @package     Kytschi\Tengu\Traits\Website\Shortcodes
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
