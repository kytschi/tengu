<?php

/**
 * Default controller.
 *
 * @package Kytschi\Tengu\Controllers\ControllerBase
 * @copyright 2021 Kytschi
 * @version 0.0.1
 *
 * Copyright 2023 Mike Welsh
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers;

use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Models\Website\Settings;
use Kytschi\Tengu\Models\Website\Stats;
use Kytschi\Tengu\Models\Website\StatsExclude;
use Kytschi\Tengu\Traits\Core\Filters;
use Kytschi\Tengu\Traits\Core\Security;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Tengu\Traits\Core\User;
use Kytschi\Tengu\Traits\Core\Validation;
use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
    use Filters;
    use Security;
    use Tags;
    use User;
    use Validation;

    public $filters = [];
    public $resource = 'page';
    public $search = '';
    public $valid_order_bys = [];
    public $default_status = 'active';
    public $page_obj = null;
    public $valid_status = [
        'active',
        'inactive',
        'deleted'
    ];

    public $valid_files = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/png',
        'image/jpe',
        'image/jpg',
        'image/avif',
        'image/gif',
        'image/bmp',
        'image/vnd.wap.wbmp',
        'image/webp'
    ];

    public function createPageObj($title = '', $sub_title = '')
    {
        $this->page_obj = new Pages([
            'name' => $title,
            'sub_title' => $sub_title,
            'updated_at' => date('Y-m-d H:i:s'),
            'meta_description' => $this->tengu->settings->meta_description,
            'meta_keywords' => $this->tagsToString($this->tengu->settings->tags),
            'meta_author' =>
                !empty($this->tengu->settings->meta_author) ?
                    $this->tengu->settings->meta_author :
                    $this->tengu->settings->name
        ]);

        $this->page_obj->sub_title = '';
    }

    public function createSlug($string)
    {
        return str_replace(
            [' '],
            '-',
            str_replace([',', '=', '&', '?', '#', ':', ';', '/', '//', '\\', '\\\\', '’'], '', strtolower($string))
        );
    }

    public function defaultCountries()
    {
        return [
            'GB' => 'United Kingdom',
        ];
    }

    public function defaultStatuses()
    {
        return $this->valid_status;
    }

    public function dump($data, bool $die = true)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        if ($die) {
            die();
        }
    }

    public function getSettings()
    {
        return (new Settings())->findFirst(
            [
                'reusable' => true,
                'limit' => '1'
            ]
        );
    }

    public function lastUpdate()
    {
        $model = $this->getSettings();
        $model->last_update = date('Y-m-d');
        if ($model->update() === false) {
            throw new SaveException(
                'Failed to update the settings',
                $model->getMessages()
            );
        }
    }

    public function redirect(string $url, $pagination = true, $old_url = false)
    {
        if ($old_url) {
            header('HTTP/1.0 301 Moved Permanently');
        }
        header('Location: ' . ($pagination ? UrlHelper::generate($url) : $url));
        exit;
    }

    public function setPageTags($page)
    {
        if (empty($page->meta_description)) {
            $page->meta_description = $this->tengu->settings->meta_description;
        }

        if (!empty($page->meta_keywords)) {
            $keywords = $page->meta_keywords;
        } else {
            $keywords = $this->tagsToString($this->tengu->settings->tags);
        }

        if (!empty($page->tags->count())) {
            $keywords .= ', ' . $this->tagsToString($page->tags);
        }
        $page->meta_keywords = $keywords;

        if (empty($page->meta_author)) {
            $page->meta_author = !empty($this->tengu->settings->meta_author) ?
                $this->tengu->settings->meta_author :
                $this->tengu->settings->name;
        }

        $page->page_updated = $page->updated_at;
        return $page;
    }

    public function setPageTitle($title)
    {
        if (empty($this->page_obj)) {
            $this->createPageObj();
        }
        $this->page_obj->name = $title;
        $this->view->setVar('page', $this->page_obj);
    }

    public function setPageSubTitle($title)
    {
        if (empty($this->page_obj)) {
            $this->createPageObj();
        }
        $this->page_obj->sub_title = $title;
        $this->view->setVar('page', $this->page_obj);
    }
}
