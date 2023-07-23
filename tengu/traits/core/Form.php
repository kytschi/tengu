<?php

/**
 * Form traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Form
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Traits\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Traits\Core\Security;

trait Form
{
    use Security;

    public function clearFormData()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }

        $controller->session->remove('form_data');
    }

    public static function getFormData($var = '', $model = null, $default = '', $encrypted = false)
    {
        $controller = new ControllerBase();
        if (!empty($controller->session)) {
            $data = $controller->session->get('form_data');
        } else {
            $data = $_GET;
        }

        $splits = explode('.', $var);

        try {
            if ($var) {
                $return = '';
                if ($data && empty($model)) {
                    $val = $data;
                    foreach ($splits as $var) {
                        $val = @$val[$var];
                        $return = @$val;
                    }
                } else {
                    $val = $model;
                    foreach ($splits as $var) {
                        $val = @$val->$var;
                        $return = @$val;
                    }

                    if ($encrypted) {
                        $return = self::decrypt($return);
                    }
                }

                if (!is_numeric($return)) {
                    return empty($return) ? $default : $return;
                } else {
                    return $return;
                }
            }
        } catch (Exception $err) {
            return $default;
        }

        return $data;
    }

    public static function getFormDefault()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return '';
        }

        $session = $controller->session;
        $data = $session->get('form_default');
        $session->remove('form_default');
        return $data;
    }

    public static function getFormDeleted()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return '';
        }

        $session = $controller->session;
        $data = $session->get('form_deleted');
        $session->remove('form_deleted');
        return $data;
    }

    public static function getFormErrors()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return '';
        }

        $session = $controller->session;
        $data = $session->get('form_errors');
        $session->remove('form_errors');
        return $data;
    }

    public static function getFormSaved()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return '';
        }

        $session = $controller->session;
        $data = $session->get('form_saved');
        $session->remove('form_saved');
        return $data;
    }

    public static function getFormUpdated()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return '';
        }

        $session = $controller->session;
        $data = $session->get('form_updated');
        $session->remove('form_updated');
        return $data;
    }

    public static function getFormWarning()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return '';
        }

        $session = $controller->session;
        $data = $session->get('form_warning');
        $session->remove('form_warning');
        return $data;
    }

    public function saveFormData($data, $excludes = [])
    {
        if ($excludes) {
            foreach ($excludes as $key) {
                unset($data[$key]);
            }
        }

        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }

        $controller->session->set('form_data', $data);
    }

    public function saveFormDefault($message = true)
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }

        $controller->session->set('form_default', $message);
    }

    public function saveFormDeleted($message = true)
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }

        $controller->session->set('form_deleted', $message);
    }

    public function saveFormError($message)
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }

        $messages = $controller->session->get('form_errors');
        if (empty($messages)) {
            $messages = [];
        }
        $messages[] = $message;
        $controller->session->set('form_errors', $messages);
    }

    public function saveFormSaved($message = true)
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }

        $controller->session->set('form_saved', $message);
    }

    public function saveFormUpdated($message = true)
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }

        $controller->session->set('form_updated', $message);
    }

    public function saveFormWarning($message = true)
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }

        $controller->session->set('form_warning', $message);
    }
}
