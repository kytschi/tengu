<?php

/**
 * Security traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Security
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

use HtmlSanitizer\Sanitizer;
use Phalcon\Encryption\Security as PhalconSecurity;

trait Security
{
    public static function cleanString($string, $full = false)
    {
        if (!is_string($string)) {
            return $string;
        }

        $ext = ['extensions' => ['basic']];
        if ($full) {
            $ext = ['extensions' => [
                'basic', 'code', 'image', 'list', 'table', 'iframe', 'details', 'extra'
            ]];
        }

        $sanitizer = Sanitizer::create($ext);
        $string = $sanitizer->sanitize($string);

        return $string;
    }

    public static function decrypt($string, $key = null)
    {
        if (empty($key)) {
            $key = file_get_contents(BASE_PATH . '/secure/tengu.pub');
        }

        if (
            $string = @openssl_decrypt(
                $string,
                'aes128',
                $key
            )
        ) {
            return $string;
        }

        return '';
    }

    public static function encrypt($string, $key = null)
    {
        if (empty($key)) {
            $key = file_get_contents(BASE_PATH . '/secure/tengu.pub');
        }

        return @openssl_encrypt(
            $string,
            'aes128',
            $key
        );
    }

    public static function hash($string)
    {
        return (new PhalconSecurity())->hash($string);
    }
}
