<?php

/**
 * User traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\User
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Traits\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\AuthorisationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Security;
use Phalcon\Encryption\Crypt;

trait User
{
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
