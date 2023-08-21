<?php

/**
 * Number helper.
 *
 * @package     Kytschi\Tengu\Helpers\NumberHelper
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

declare(strict_types=1);

namespace Kytschi\Tengu\Helpers;

class NumberHelper
{
    public static function currencySymbol()
    {
        return str_replace('0.00', '', self::toCurrency(0));
    }

    public static function fromCurrency($number)
    {
        if (empty($number)) {
            return 0.00;
        }
        return floatval(preg_replace('/[^\d\.]/', '', $number));
    }

    public static function toCurrency($number, $currency = TENGU_CURRENCY)
    {
        if (empty($number)) {
            $number = 0.00;
        }
        $locale = 'en_GB';
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency(floatval($number), $currency);
    }

    public static function taxLabel($currency = '')
    {
        if (empty($currency)) {
            $currency = TENGU_CURRENCY;
        }
        switch ($currency) {
            case 'GBP':
                return 'VAT';
                break;
            default:
                return 'TAX';
                break;
        }
    }

    public static function to2DP($number)
    {
        if (empty($number)) {
            $number = 0.00;
        }
        return number_format(floatval($number), 2, '.', '');
    }

    public static function pretty($number)
    {
        if (empty($number)) {
            $number = 0.00;
        }
        return number_format(floatval($number));
    }
}
