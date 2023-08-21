<?php

/**
 * Default controller.
 *
 * @package Kytschi\Tengu\Controllers\ControllerBase
 * @copyright 2021 Kytschi
 * @version 0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers;

use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Website\Settings;
use Kytschi\Tengu\Models\Website\Stats;
use Kytschi\Tengu\Models\Website\StatsExclude;
use Kytschi\Tengu\Traits\Core\Filters;
use Kytschi\Tengu\Traits\Core\Security;
use Kytschi\Tengu\Traits\Core\User;
use Kytschi\Tengu\Traits\Core\Validation;
use Phalcon\Mvc\Controller;
use Phalcon\Tag;

class ControllerBase extends Controller
{
    use Filters;
    use Security;
    use User;
    use Validation;

    public $filters = [];
    public $resource = 'page';
    public $search = '';
    public $valid_order_bys = [];
    public $default_status = 'active';
    public $valid_status = [
        'active',
        'inactive',
        'deleted'
    ];

    public $valid_files = [
        'application/pdf',
        'image/jpeg',
        'image/png'
    ];

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
    }

    public function setPageTitle($title)
    {
        Tag::setTitle($title);
    }

    public function setPageSubTitle($title)
    {
        Tag::setDefault('sub_title', $title);
    }
}
