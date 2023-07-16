<?php

/**
 * Url helper.
 *
 * @package     Kytschi\Tengu\Helpers\UrlHelper
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Helpers;

use Kytschi\Tengu\Traits\Core\Pagination;

class UrlHelper
{
    use Pagination;

    public static function activeEntry(string $match, bool $boolean = false)
    {
        if (!empty($_GET['entry']) && $_GET['entry'] == $match) {
            return ($boolean) ? 'true' : 'show';
        }

        return ($boolean) ? 'false' : '';
    }

    public static function activeTab(string $match, string $default, bool $boolean = false)
    {
        if (empty($_GET['tab']) && $match == $default) {
            return ($boolean) ? 'true' : 'active';
        } elseif (!empty($_GET['tab']) && $_GET['tab'] == $match) {
            return ($boolean) ? 'true' : 'active';
        }

        return ($boolean) ? 'false' : '';
    }

    public static function api(string $url, string $version = 'v1')
    {
        return rtrim($_ENV['APP_TENGU_URL'], '/') . '/' .
            trim($_ENV['APP_TENGU_API_URL'], '/') . '/' .
            ($version ? $version . '/' : '') .
            ltrim($url, '/');
    }

    public static function append($url, $string)
    {
        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }

        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $url .= $key . '=' . $value . '&';
            }

            return rtrim($url, '&');
        }
        return $url . $string;
    }

    public static function backend(string $url, string $from = '')
    {
        $url = rtrim($_ENV['APP_TENGU_URL'], '/') . '/' . ltrim($url, '/');

        if ($from) {
            if ($from == 'here') {
                $parsed = parse_url($_SERVER['REQUEST_URI']);
                $path = '/' . trim($parsed['path'], '/');

                $url = self::append($url, 'from=' . $path);
            } else {
                $url = self::append($url, 'from=' . $from);
            }
        }
        
        return $url;
    }

    public static function contains(string $url)
    {
        $parsed = parse_url($_SERVER['REQUEST_URI']);

        $path = '/' . trim(ltrim($parsed['path'], $_ENV['APP_TENGU_URL']), '/');
        
        if ($url == '/') {
            return  ($path == $url) ? true : false;
        }

        return (strpos($path, $url) !== false) ? true : false;
    }

    public static function from(string $url)
    {
        $url = rtrim($url, '/');

        $parsed = parse_url($_SERVER['REQUEST_URI']);
        $path = '/' . trim($parsed['path'], '/');

        $url .= '?from=' . $path;
        
        return $url;
    }

    public static function get()
    {
        $parsed = parse_url($_SERVER['REQUEST_URI']);

        return '/' . trim(ltrim($parsed['path'], $_ENV['APP_TENGU_URL']), '/');
    }

    public static function generate(string $url, array $params = [], $pagination = true)
    {
        try {
            if (strpos($url, '?') === false) {
                $url .= '?';
            } else {
                $url .= '&';
            }

            if ($pagination) {
                if (($page = self::getPagination('page')) && empty($params['page'])) {
                    $add = 'page=' . $page;
                    if (strpos($url, $add) === false) {
                        $url .= $add . '&';
                    }
                }

                if (($limit = self::getPagination('limit')) && empty($params['limit'])) {
                    $add = 'limit=' . $limit;
                    if (strpos($url, $add) === false) {
                        $url .= $add . '&';
                    }
                }

                if (($order_by = self::getPagination('order_by')) && empty($params['order_by'])) {
                    $add = 'order_by=' . $order_by;
                    if (strpos($url, $add) === false) {
                        $url .= $add . '&';
                    }
                }

                if (($order_dir = self::getPagination('order_dir')) && empty($params['order_dir'])) {
                    $add = 'order_dir=' . $order_dir;
                    if (strpos($url, $add) === false) {
                        $url .= $add . '&';
                    }
                }

                if (($search = self::getPagination('search')) && empty($params['search'])) {
                    $add = 'search=' . $search;
                    if (strpos($url, $add) === false) {
                        $url .= $add . '&';
                    }
                }

                if (($filter = self::getPagination('filter')) && empty($params['filter'])) {
                    $add = 'filter=' . $filter;
                    if (strpos($url, $add) === false) {
                        $url .= $add . '&';
                    }
                }

                if ($filters = self::getPagination('filters')) {
                    if (is_array($filters)) {
                        foreach ($filters as $key => $filter) {
                            $add = 'filters[' . $key . ']=' . $filter;
                            if (strpos($url, $add) === false) {
                                $url .= $add . '&';
                            }
                        }
                    }
                }
            }

            if (!empty($_GET['tab'])) {
                $add = 'tab=' . $_GET['tab'];
                if (strpos($url, $add) === false) {
                    $url .= $add . '&';
                }
            }

            if (!empty($_GET['search'])) {
                $add = 'search=' . $_GET['search'];
                if (strpos($url, $add) === false) {
                    $url .= $add . '&';
                }
            }

            foreach ($params as $key => $param) {
                $add = $key . '=' . $param;
                if (strpos($url, $add) === false) {
                    $url .= $add . '&';
                }
            }

            return rtrim(rtrim($url, '&'), '?');
        } catch (\Exception $err) {
            echo $err->getMessage();
        }
    }

    public static function isBackend()
    {
        $parsed = parse_url($_SERVER['REQUEST_URI']);
        return strpos($parsed['path'], rtrim($_ENV['APP_TENGU_URL'], '/') . '/') !== false ? true : false;
    }

    public static function matches(string $url)
    {
        $parsed = parse_url($_SERVER['REQUEST_URI']);

        $path = '/' . trim(ltrim($parsed['path'], $_ENV['APP_TENGU_URL']), '/');

        if ($url == '/') {
            return  ($path == $url) ? true : false;
        }

        return (substr($path, 0, strlen($url)) == $url) ? true : false;
    }

    public static function orderBy(string $url, string $order_by, string $order_dir = 'asc')
    {
        return self::generate(
            $url,
            [
                'order_by' => $order_by,
                'order_dir' => $order_dir
            ]
        );
    }
}
