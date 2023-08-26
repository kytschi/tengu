<?php

/**
 * User trait.
 *
 * @package     Kytschi\Tengu\Traits\Core\User
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

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ClientException;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\AuthorisationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Security;
use Phalcon\Encryption\Crypt;

trait User
{
    use Logs;
    use Security;

    public static function getUserIp()
    {
        $splits = explode('.', $_SERVER['REMOTE_ADDR']);
        $key = '';
        foreach ($splits as $split) {
            $key .= dechex(intval($split));
        }
        return self::encrypt($key);
    }

    public static function getUserLocationByIp()
    {
        if ($_ENV['APP_ENV'] == 'local') {
            $output = 'GeoIP Country Edition: GB, United Kingdom';
        } else {
            $output = shell_exec("geoiplookup " . $_SERVER['REMOTE_ADDR']);
        }
        if ($output) {
            if (strpos($output, 'IP Address not found') !== false) {
                return null;
            }
            $splits = explode(":", $output);
            $splits = explode(",", $splits[count($splits) - 1]);
            unset($splits[0]);
            $output = trim(implode(",", $splits));
            return ($output) ? $output : null;
        }

        return null;
    }

    public static function getUserGeolocationByIp()
    {
        $object = new \stdClass();
        $object->country_name = null;
        $object->latitude = null;
        $object->longitude = null;

        if ($_ENV['APP_ENV'] == 'local') {
            return $object;
        }

        if (empty($_SERVER['REMOTE_ADDR'])) {
            return $object;
        }

        $guzzle = new Guzzle(
            [
                'base_uri' => 'https://freegeoip.app',
                'verify' => false
            ]
        );

        try {
            $res = $guzzle->request(
                'GET',
                '/json/' . $_SERVER['REMOTE_ADDR']
            );

            $object = json_decode($res->getBody()->getContents());
        } catch (ClientException $err) {
            (new self())->addLog(
                'user-geo-location',
                null,
                'error',
                $err->getMessage()
            );
            return $object;
        }
        return $object;
    }

    public static function getUser($var = '')
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return null;
        }

        $return = $controller->session->get('user');
        if ($var) {
            $return = isset($return->$var) ? $return->$var : null;
        }

        return $return;
    }

    public static function getUserFullName()
    {
        if ($name = self::getUser('full_name')) {
            return $name;
        }
        return '';
    }

    public static function getUserId()
    {
        if (!TENGU_BACKEND) {
            return '00000000-0000-0000-0000-000000000000';
        }
        if ($id = self::getUser('id')) {
            return $id;
        }

        return '00000000-0000-0000-0000-000000000000';
    }

    public static function isAdmin()
    {
        $user = self::getUser();
        if (empty($user)) {
            return false;
        }

        if (empty($user->group)) {
            return false;
        }

        if (in_array($user->group, ['administrator', 'super-user'])) {
            return true;
        }

        return false;
    }

    public static function isLoggedIn()
    {
        return !empty(self::getUser()) ? true :  false;
    }

    public static function isSU()
    {
        $user = self::getUser();
        if (empty($user)) {
            return false;
        }

        if (empty($user->group)) {
            return false;
        }

        if ($user->group == 'super-user') {
            return true;
        }

        return false;
    }

    public function logout()
    {
        $_SESSION = [];
        @session_destroy();

        $controller = new ControllerBase();

        if (!empty($this->session)) {
            $this->session->destroy();
        } elseif (!empty($controller->session)) {
            $controller->session->destroy();
        }

        if (strpos($_SERVER['REQUEST_URI'], UrlHelper::backend('/login')) === false) {
            $controller->redirect(UrlHelper::backend('/login'));
        }
    }

    public function secure(array $groups = [])
    {
        $user = self::getUser();

        if (empty($user)) {
            $this->redirect(
                UrlHelper::backend('/login?from=' . urlencode($_SERVER['REQUEST_URI']))
            );
        }

        if (!empty($groups)) {
            if (empty($user->group)) {
                throw new AuthorisationException('Invalid group');
            }

            $groups[] = 'super-user';
            if (!in_array($user->group, $groups)) {
                throw new AuthorisationException('You do not have permission to access this page');
            }
        }
    }
}
