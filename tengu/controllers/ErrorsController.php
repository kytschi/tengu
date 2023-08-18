<?php

/**
 * Errors controller.
 *
 * @package     Kytschi\Tengu\Controllers\ErrorsController
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

namespace Kytschi\Tengu\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\GenericException;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Tags;

class ErrorsController extends ControllerBase
{
    use Logs;
    use Tags;

    public function accessDeninedAction()
    {
        $this->setPageTitle('Access denined');
        $this->setDefaults();

        $return = $this->getReturn();
        $this->view->setVar('message', $return['message']);
        $this->view->setVar('data', $return['data']);

        return $this->view->partial('errors/access_denined');
    }

    public function criticalAction()
    {
        $this->setPageTitle('Serious error');
        $this->setDefaults();

        $return = $this->getReturn();

        $this->view->setVar('message', $return['message']);
        $this->view->setVar('data', $return['data']);

        if (!file_exists(($this->di->getConfig())->application->siteViewsDir . '/errors/critical.phtml')) {
            $this->display($return['message']);
        }

        return $this->view->partial('errors/critical');
    }

    public function display($message)
    {
        $err = new GenericException($message);
        echo $err;
        die();
    }

    private function getReturn()
    {
        $return = [
            'message' => '',
            'data' => null
        ];

        if ($error = $this->getError()) {
            $return['message'] = $error->getMessage();
            if (method_exists($error, 'getData')) {
                $return['data'] = $error->getData();
            }

            if (is_array($return['data'])) {
                $tmp = $return['data'];
                $return['data'] = '';
                foreach ($tmp as $item) {
                    $return['data'] .= $item->getMessage() . '<br/>';
                }
            }
        } elseif (!empty($_GET['message'])) {
            $return['message'] = strip_tags(urldecode($_GET['message']));
        }

        return $return;
    }

    public function saveAction()
    {
        $this->setPageTitle('Save error');
        $this->setDefaults();

        $return = $this->getReturn();
        $this->view->setVar('message', $return['message']);
        $this->view->setVar('data', $return['data']);

        return $this->view->partial('errors/save');
    }

    private function setDefaults()
    {
        $page = new \stdClass();
        $page->name = 'Page not found';
        $page->page_updated = date('Y-m-d H:i:s');
        $page->meta_description = $this->tengu->settings->meta_description;
        $page->meta_keywords = $this->tagsToString($this->tengu->settings->tags);
        $page->meta_author = !empty($this->tengu->settings->meta_author) ?
            $this->tengu->settings->meta_author :
            $this->tengu->settings->name;

        $this->view->setVar('page', $page);
    }
}
