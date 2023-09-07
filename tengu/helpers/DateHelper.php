<?php

/**
 * Date helper.
 *
 * @package     Kytschi\Tengu\Helpers\DateHelper
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

/**
 * Find Date in a String
 *
 * @author   Etienne Tremel
 * @license  http://creativecommons.org/licenses/by/3.0/ CC by 3.0
 * @link     http://www.etiennetremel.net
 * @version  0.2.0
 *
 * @param string  find_date( ' some text 01/01/2012 some text' ) or find_date( ' some text October 5th 86 some text' )
 * @return mixed  false if no date found else array: array( 'day' => 01, 'month' => 01, 'year' => 2012 )
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Helpers;

class DateHelper
{
    public static function find($string, $default_year = '')
    {
        // Define month name:
        $month_names = array(
            "january",
            "february",
            "march",
            "april",
            "may",
            "june",
            "july",
            "august",
            "september",
            "october",
            "november",
            "december"
        );

        $short_month_names = array(
            "jan",
            "feb",
            "mar",
            "apr",
            "may",
            "jun",
            "jul",
            "aug",
            "sep",
            "oct",
            "nov",
            "dec"
        );

        // Define day name
        $day_names = array(
            "monday",
            "tuesday",
            "wednesday",
            "thursday",
            "friday",
            "saturday",
            "sunday"
        );

        $short_day_names = array(
            "mon",
            "tue",
            "wed",
            "thur",
            "fri",
            "sat",
            "sun"
        );

        // Define ordinal number
        $ordinal_number = ['st', 'nd', 'rd', 'th'];

        $day = '';
        $month = '';
        $year = '';
        $no_year = false;

        // Match dates: 01/01/2012 or 30-12-11 or 1 2 1985
        preg_match('/([0-9]?[0-9])[\.\-\/ ]+([0-1]?[0-9])[\.\-\/ ]+([0-9]{2,4})/', $string, $matches);
        if ($matches) {
            if ($matches[1]) {
                $day = $matches[1];
            }
            if ($matches[2]) {
                $month = $matches[2];
            }
            if ($matches[3]) {
                $year = $matches[3];
            }
        }

        if (empty($day) || empty($month)) {
            // Match dates: Sunday 1st March; Sunday, 1 March; Sun 1 Mar; Sun-1-March
            preg_match(
                '/(?:(?:' .
                implode('|', $day_names) . '|' .
                implode('|', $short_day_names) .
                ')[ ,\-_\/]*)?([0-9]?[0-9])[ ,\-_\/]*(?:' .
                implode('|', $ordinal_number) .
                ')?[ ,\-_\/]*(' .
                implode('|', $month_names) . '|' .
                implode('|', $short_month_names) .
                ')[ ,\-_\/]/i',
                $string,
                $matches
            );
            if ($matches) {
                if (empty($day) && $matches[1]) {
                    $day = $matches[1];
                }

                if (empty($month) && $matches[2]) {
                    $month = array_search(strtolower($matches[2]), $short_month_names);
                }

                if (!$month) {
                    $month = array_search(strtolower($matches[2]), $month_names);
                }

                $month = $month + 1;
                $no_year = true;
            }
        }

        if ((empty($day) || empty($month) || empty($year)) && !$no_year) {
            // Match dates: Sunday 1st March 2015; Sunday, 1 March 2015; Sun 1 Mar 2015; Sun-1-March-2015
            preg_match(
                '/(?:(?:' .
                implode('|', $day_names) . '|' .
                implode('|', $short_day_names) .
                ')[ ,\-_\/]*)?([0-9]?[0-9])[ ,\-_\/]*(?:' .
                implode('|', $ordinal_number) .
                ')?[ ,\-_\/]*(' .
                implode('|', $month_names) . '|' .
                implode('|', $short_month_names) .
                ')[ ,\-_\/]+([0-9]{4})/i',
                $string,
                $matches
            );
            if ($matches) {
                if (empty($day) && $matches[1]) {
                    $day = $matches[1];
                }

                if (empty($month) && $matches[2]) {
                    $month = array_search(strtolower($matches[2]), $short_month_names);
                }

                if (!$month) {
                    $month = array_search(strtolower($matches[2]), $month_names);
                }

                $month = $month + 1;
                if (empty($year) && $matches[3]) {
                    $year = $matches[3];
                }
            }
        }

        if ((empty($day) || empty($month) || empty($year)) && !$no_year) {
            // Match dates: March 1st 2015; March 1 2015; March-1st-2015
            preg_match(
                '/(' . implode('|', $month_names) . '|' .
                implode('|', $short_month_names) .
                ')[ ,\-_\/]*([0-9]?[0-9])[ ,\-_\/]*(?:' .
                implode('|', $ordinal_number) .
                ')?[ ,\-_\/]+([0-9]{4})/i',
                $string,
                $matches
            );
            if ($matches) {
                if (empty($month) && $matches[1]) {
                    $month = array_search(strtolower($matches[1]), $short_month_names);

                    if (!$month) {
                        $month = array_search(strtolower($matches[1]), $month_names);
                    }

                    $month = $month + 1;
                }

                if (empty($day) && $matches[2]) {
                    $day = $matches[2];
                }

                if (empty($year) && $matches[3]) {
                    $year = $matches[3];
                }
            }
        }

        // Match month name:
        if (empty($month)) {
            preg_match('/(' . implode('|', $month_names) . ')/i', $string, $matches_month_word);

            if ($matches_month_word && $matches_month_word[1]) {
                $month = array_search(strtolower($matches_month_word[1]), $month_names);
            }

            // Match short month names
            if (empty($month)) {
                preg_match('/(' . implode('|', $short_month_names) . ')/i', $string, $matches_month_word);
                if ($matches_month_word && $matches_month_word[1]) {
                    $month = array_search(strtolower($matches_month_word[1]), $short_month_names);
                }
            }

            if (!empty($month)) {
                $month = $month + 1;
            }
        }

        // Match 5th 1st day:
        if (empty($day)) {
            preg_match('/([0-9]?[0-9])(' . implode('|', $ordinal_number) . ')/', $string, $matches_day);
            if ($matches_day && $matches_day[1]) {
                $day = $matches_day[1];
            }
        }

        // Match Year if not already setted:
        if (empty($year) && !$no_year) {
            preg_match('/[0-9]{4}/', $string, $matches_year);
            if ($matches_year && $matches_year[0]) {
                $year = $matches_year[0];
            }
        }

        if (!empty($day) && ! empty($month) && empty($year) && !$no_year) {
            preg_match('/[0-9]{2}/', $string, $matches_year);
            if ($matches_year && $matches_year[0]) {
                $year = $matches_year[0];
            }
        }

        // Day leading 0
        if (intval($day) < 10) {
            $day = '0' . $day;
        }

        // Month leading 0
        if (intval($month) < 10) {
            $month = '0' . $month;
        }

        // Check year:
        if (2 == strlen($year) && $year > 20) {
            $year = '19' . $year;
        } elseif (2 == strlen($year) && $year < 20) {
            $year = '20' . $year;
        }

        if (empty($year)) {
            $year = $default_year;
        }

        $date = array(
            'year'  => $year,
            'month' => $month,
            'day'   => $day
        );

        // Return false if nothing found:
        if (!intval($year) || !intval($month) || !intval($day)) {
            return false;
        } else {
            return $date;
        }
    }
    public static function ical($datetime)
    {
        return (new \DateTime($datetime))->format('Ymd\THis\Z');
    }

    public static function iso($datetime)
    {
        return (new \DateTime($datetime))->format(\DateTime::ATOM);
    }

    public static function meta($datetime)
    {
        try {
            return date('l, F d, H:i a', strtotime($datetime));
        } catch (\Exception $err) {
            return 'Failed to render the date';
        }
    }

    public static function numberOfDays($start, $end)
    {
        try {
            $start_date = new \DateTime($start);
            $end_date = new \DateTime($end);

            $diff = $start_date->diff($end_date);
            return intval($diff->format("%a"));
        } catch (\Exception $err) {
            return 'Failed to get days';
        }
    }

    public static function numberOfHours($start, $end)
    {
        try {
            if (empty($start) || empty($end)) {
                return 0;
            }

            $start_date = new \DateTime($start);
            $end_date = new \DateTime($end);

            $diff = $start_date->diff($end_date);

            $return = intval($diff->format("%h"));
            if (intval($diff->format("%i")) >= 30) {
                $return .= '.5';
            }

            return $return;
        } catch (\Exception $err) {
            return 'Failed to get days';
        }
    }

    public static function pretty($datetime, $time = true, $today = false, $unknown = 'Unknown', $seconds = true)
    {
        try {
            if (empty($datetime)) {
                if ($today) {
                    return $today ? date($time ? ($seconds ? 'd/m/Y H:i:s' : 'd/m/Y H:i') : 'd/m/Y') : $unknown;
                }
                return $unknown;
            }

            if (strtolower($datetime) == 'unknown') {
                return $unknown;
            }
            $timestamp = strtotime((string) $datetime);
            if (empty($timestamp)) {
                $timestamp = strtotime('NOW');
            }
            return date(
                $time ? ($seconds ? 'd/m/Y H:i:s' : 'd/m/Y H:i') : 'd/m/Y',
                $timestamp
            );
        } catch (\Exception $err) {
            return 'Failed to render the date';
        }
    }

    public static function prettyFull($datetime, $time = true, $today = false, $unknown = 'Unknown', $seconds = true)
    {
        try {
            if (empty($datetime)) {
                if ($today) {
                    return $today ? date($time ? ($seconds ? 'M dS, Y H:i:s' : 'M dS, Y H:i') : 'M dS, Y') : $unknown;
                }
                return $unknown;
            }
            if (strtolower($datetime) == 'unknown') {
                return $unknown;
            }

            $timestamp = strtotime((string) $datetime);
            if (empty($timestamp)) {
                $timestamp = strtotime('NOW');
            }
            return date($time ? 'M jS, Y H:i' : 'M jS, Y', $timestamp);
        } catch (\Exception $err) {
            return 'Failed to render the date';
        }
    }

    public static function sql($datetime, $time = true)
    {
        try {
            if (empty($datetime)) {
                return null;
            }

            if (strtolower($datetime) == 'unknown') {
                return null;
            }

            if (is_numeric($datetime)) {
                return null;
            }

            if (!$datetime) {
                return null;
            }

            if ($time) {
                $splits = explode(' ', $datetime);
                if (isset($splits[1])) {
                    $splits = explode(':', $splits[1]);
                    if (count($splits) < 3) {
                        $datetime .= ':00';
                    }
                }
            } elseif (strpos($datetime, ':') !== false) {
                $datetime = explode(' ', $datetime)[0];
            }

            if (strpos($datetime, '/') !== false) {
                $date = \DateTime::createFromFormat(($time ? 'd/m/Y H:i:s' : 'd/m/Y'), $datetime);
                if (empty($date)) {
                    return null;
                }
                if (substr($date->format('Y'), 0, 2) == '00') {
                    $date = \DateTime::createFromFormat(($time ? 'd/m/y H:i:s' : 'd/m/y'), $datetime);
                }
                return $date->format($time ? 'Y-m-d H:i:s' : 'Y-m-d');
            }

            if (!($date = strtotime($datetime))) {
                return null;
            }

            return date(($time ? 'Y-m-d H:i:s' : 'Y-m-d'), $date);
        } catch (\Exception $err) {
            return 'Failed to render the date';
        }
    }

    public static function sqlStripSeconds($datetime)
    {
        try {
            if (empty($datetime)) {
                return '';
            }

            return date('d-m-Y H:i', strtotime($datetime));
        } catch (\Exception $err) {
            return 'Failed to render the date';
        }
    }

    public static function timeOnly($datetime, $seconds = false, $unknown = 'Unknown')
    {
        try {
            if (empty($datetime)) {
                if ($today) {
                    return $today ? date($time ? ($seconds ? 'H:i:s' : 'H:i') : 'd/m/Y') : $unknown;
                }
                return $unknown;
            }

            if (strtolower($datetime) == 'unknown') {
                return $unknown;
            }
            return date(($seconds ? 'H:i:s' : 'H:i'), strtotime((string) $datetime));
        } catch (\Exception $err) {
            return 'Failed to render the date';
        }
    }
}
