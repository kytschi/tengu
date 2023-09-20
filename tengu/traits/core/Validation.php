<?php

/**
 * Validation traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Validation
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Tengu\Traits\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Controllers\ErrorsController;
use Kytschi\Tengu\Exceptions\AuthorisationException;
use Kytschi\Tengu\Helpers\StringHelper;

trait Validation
{
    public static function clearValidationError()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }

        $controller->session->remove('error');
        $controller->session->remove('form_error');
    }

    public static function generateTSC()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return '';
        }

        $code = StringHelper::random(64);
        $controller->session->set('_TSC', $code);
        return $code;
    }

    public static function getValidationError()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return '';
        }
        return $controller->session->get('form_error');
    }

    public function isValidStatus($status)
    {
        if (!in_array($status, $this->valid_status)) {
            return $this->default_status;
        }

        return $status;
    }

    public function getError()
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return '';
        }
        return $controller->session->get('error');
    }

    public function notFound(string $message = 'Entry not found')
    {
        header("HTTP/1.0 404 Not Found");

        $page = new \stdClass();
        $page->name = 'Page not found';
        $page->page_updated = date('Y-m-d H:i:s');
        $page->meta_description = $this->tengu->settings->meta_description;
        $page->meta_keywords = $this->tagsToString($this->tengu->settings->tags);
        $page->sub_title = '';
        $page->meta_author = !empty($this->tengu->settings->meta_author) ?
            $this->tengu->settings->meta_author :
            $this->tengu->settings->name;

        if (!file_exists(($this->di->getConfig())->application->siteViewsDir . '/errors/not_found.phtml')) {
            (new ErrorsController())->display($message);
        }
        $this->view->setVar('page', $page);
        return (new ControllerBase())->view->partial(
            'errors/not_found',
            [
                'message' => $message
            ]
        );
    }

    public function saveError($err)
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }
        $controller->session->set('error', $err);
    }

    public function saveValidationError($err)
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return '';
        }

        $controller->session->set('form_error', $err);
    }

    public function validTSC($tsc)
    {
        $controller = new ControllerBase();
        if (empty($controller->session)) {
            return;
        }

        $check = $controller->session->get('_TSC');
        $controller->session->remove('_TSC');

        if ($tsc != $check) {
            throw new AuthorisationException('Invalid form authentication key');
        }
    }
}
