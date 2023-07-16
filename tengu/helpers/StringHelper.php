<?php

/**
 * String helper.
 *
 * @package     Kytschi\Tengu\Helpers\StringHelper
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Helpers;

use Kytschi\Tengu\Controllers\Core\FormController;
use Kytschi\Tengu\Traits\Core\Security;

class StringHelper
{
    use Security;

    public static function count($source, $word)
    {
        return substr_count(strtolower($source), strtolower($word));
    }

    /**
     * Generate a random string.
     */
    public static function random(
        int $length = 64,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string {
        /*
         * If the length is less than one, throw an error.
         */
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }

        /*
         * Define the pieces array.
         */
        $pieces = [];

        /*
         * Generate a max length passed on the keyspace.
         */
        $max = mb_strlen($keyspace, '8bit') - 1;

        /*
         * Loop through and build pieces.
         */
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }

        /*
         * Implode the pieces and return the random string.
         */
        return implode('', $pieces);
    }

    /**
     * Truncate the string.
     *
     * @param string $string
     * @param int $length
     * @return string $string
     */
    public static function truncate(string $string, int $length = 50)
    {
        return substr($string, 0, $length) . '...';
    }

    public static function toColour($string, $rgb = false)
    {
        $hex = '#' . substr(dechex(crc32($string)), 0, 6);
        
        if ($rgb) {
            list($red, $green, $blue) = sscanf($hex, "#%02x%02x%02x");
            return $red . ',' . $green . ',' . $blue;
        }

        return $hex;
    }

    public static function toYesNo($val)
    {
        return $val ? 'Yes' : 'No';
    }
}
