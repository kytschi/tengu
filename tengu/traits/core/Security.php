<?php

/**
 * Security traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Security
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
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
