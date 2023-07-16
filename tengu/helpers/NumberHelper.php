<?php

/**
 * Number helper.
 *
 * @package     Kytschi\Tengu\Helpers\NumberHelper
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
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
