<?php

/**
 * Pages model.
 *
 * @package     Kytschi\Tengu\Models\Website\Pages
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

namespace Kytschi\Tengu\Models\Website;

use Kytschi\Makabe\Models\Campaigns;
use Kytschi\Makabe\Models\Keywords;
use Kytschi\Makabe\Models\Personas;
use Kytschi\Makabe\Models\PersonaPages;
use Kytschi\Makabe\Models\SpunContent;
use Kytschi\Makabe\Models\Surveys;
use Kytschi\Makabe\Models\SurveySteps;
use Kytschi\Makabe\Models\UserSurveys;
use Kytschi\Phoenix\Models\Products;
use Kytschi\Phoenix\Models\Settings;
use Kytschi\Tengu\Controllers\Website\BlogPostsController;
use Kytschi\Tengu\Controllers\Website\PortfolioController;
use Kytschi\Tengu\Controllers\Website\PagesController;
use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\OldUrls;
use Kytschi\Tengu\Models\Website\PageCategories;
use Kytschi\Tengu\Models\Website\PageFiles;
use Kytschi\Tengu\Models\Website\Stats;
use Kytschi\Tengu\Models\Website\Templates;

class Pages extends Model
{
    public $template_id;
    public $parent_id;
    public $name;
    public $url;
    public $summary;
    public $content;
    public $slogan;
    public $meta_keywords;
    public $meta_description;
    public $meta_author;
    public $canonical_url;
    public $type = 'page';
    public $status;
    public $searchable = 1;
    public $search_tags;
    public $sitemap = 1;
    public $cover_image_id;
    public $banner_image_id;
    public $spinnable = 0;
    public $spin_label;
    public $spin_content;
    public $pre_spin_content;
    public $pre_spin_name;
    public $last_spun;
    public $spins;
    public $spin_content_id;
    public $sort = 0;
    public $feature = 0;
    public $rating;
    public $event_on;
    public $event_end;
    public $event_recurring;
    public $event_location;
    public $postcode;
    public $longitude;
    public $latitude;
    public $external_contact_form;

    public function initialize()
    {
        $this->hasOne(
            'template_id',
            Templates::class,
            'id',
            [
                'alias'    => 'template',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'updated_by',
            Users::class,
            'id',
            [
                'alias'    => 'updated',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'created_by',
            Users::class,
            'id',
            [
                'alias'    => 'created',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'deleted_by',
            Users::class,
            'id',
            [
                'alias'    => 'deleted',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'campaign_id',
            Campaigns::class,
            'id',
            [
                'alias'    => 'campaign',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'parent_id',
            self::class,
            'id',
            [
                'alias'    => 'parent',
                'reusable' => true
            ]
        );

        $this->hasMany(
            'id',
            self::class,
            'parent_id',
            [
                'alias'    => 'children',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );

        $this->hasOne(
            'cover_image_id',
            Files::class,
            'id',
            [
                'alias'    => 'cover_image',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );

        $this->hasOne(
            'banner_image_id',
            Files::class,
            'id',
            [
                'alias'    => 'banner_image',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );

        $this->hasMany(
            'id',
            OldUrls::class,
            'resource_id',
            [
                'alias'    => 'old_urls',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );

        $this->hasManyToMany(
            'id',
            PageCategories::class,
            'page_id',
            'category_id',
            Pages::class,
            'id',
            [
                'alias'    => 'categories',
                'reusable' => true,
                'params'   => [
                    'conditions' => PageCategories::class . '.deleted_at IS NULL',
                    'order' => PageCategories::class . '.sort ASC'
                ]
            ]
        );

        $this->hasManyToMany(
            'id',
            PageCategories::class,
            'category_id',
            'page_id',
            Pages::class,
            'id',
            [
                'alias'    => 'category_items',
                'reusable' => true,
                'params'   => [
                    'conditions' => PageCategories::class . '.deleted_at IS NULL',
                    'order' => PageCategories::class . '.sort ASC, ' . PageCategories::class . '.created_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Tags::class,
            'resource_id',
            [
                'alias'    => 'tags',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL AND 
                        (resource="page" OR resource="portfolio" OR resource="blog-post") AND 
                        type IS NULL',
                    'order' => 'tag'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Logs::class,
            'resource_id',
            [
                'alias'    => 'logs',
                'reusable' => false,
                'params'   => [
                    'order' => 'created_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Notes::class,
            'resource_id',
            [
                'alias'    => 'notes',
                'reusable' => false,
                'params'   => [
                    'order' => 'created_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            PageFiles::class,
            'page_id',
            [
                'alias'    => 'carousel',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL AND resource="carousel"',
                    'order' => 'created_at ASC'
                ]
            ]
        );

        $this->hasOne(
            'id',
            SpunContent::class,
            'resource_id',
            [
                'alias'    => 'current_spun_content',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'status = "live" AND deleted_at IS NULL'
                ]
            ]
        );

        $this->hasOneThrough(
            'id',
            PersonaPages::class,
            'page_id',
            'persona_id',
            Personas::class,
            'id',
            [
                'alias'    => 'persona',
                'reusable' => true,
                'params'   => [
                    'conditions' => PersonaPages::class . '.deleted_at IS NULL'
                ]
            ]
        );

        $this->hasOne(
            'id',
            Products::class,
            'page_id',
            [
                'alias'    => 'product',
                'reusable' => true,
            ]
        );

        $this->hasMany(
            'id',
            SurveySteps::class,
            'survey_id',
            [
                'alias'    => 'steps',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'created_at'
                ]
            ]
        );

        $this->hasOne(
            'id',
            SurveySteps::class,
            'page_id',
            [
                'alias'    => 'step',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'id',
            Surveys::class,
            'page_id',
            [
                'alias'    => 'survey',
                'reusable' => true
            ]
        );

        $this->hasMany(
            'id',
            UserSurveys::class,
            'page_id',
            [
                'alias'    => 'surveys',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'created_at DESC'
                ]
            ]
        );
    }

    public function canOrder()
    {
        $settings = (new Settings())->findFirst([
            'conditions' => 'id IS NOT NULL'
        ]);

        $stock = !empty($this->product) ? $this->product->stock : 0;
        if ($stock) {
            return true;
        }

        return ($settings->zero_stock && !$stock) ? true : false;
    }

    public function children($limit = 0, $exclude = null, $random = false, $featured = false)
    {
        return $this->category_items->filter(
            function ($page) use ($exclude) {
                if ($page->id != $exclude) {
                    return $page;
                }
            }
        );
    }

    public function current()
    {
        $survey = (new UserSurveys())->findFirst([
            'conditions' => 'page_id = :page_id: AND created_by = :created_by:',
            'bind' => [
                'page_id' => $this->id,
                'created_by' => self::getUserIp(),
            ]
        ]);
        if (empty($survey)) {
            return null;
        }

        return $survey->current;
    }

    public function getCategory()
    {
        if (empty($this->categories)) {
            return null;
        }

        foreach ($this->categories as $category) {
            if (UrlHelper::contains($category->real_url)) {
                return $category;
            }
        }

        return null;
    }

    public function getCode()
    {
        return !empty($this->product) ? $this->product->code : '';
    }

    public function getKeywords()
    {
        return (new Keywords())->find([
            'conditions' => '(resource_id = :id: OR resource_id IS NULL) AND deleted_at IS NULL',
            'bind' => [
                'id' => $this->id
            ],
            'order' => 'rank DESC, keyword ASC'
        ]);
    }

    public function getLowStock()
    {
        return !empty($this->product) ? $this->product->low_stock : 0;
    }

    public function getPrice()
    {
        return !empty($this->product) ? $this->product->price : 0.00;
    }

    public function getResourceType()
    {
        return $this->type;
    }

    public function getSpunCount()
    {
        $model = new Stats();
        $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(
                'SELECT COUNT(id) AS total FROM ' . (new SpunContent())->getSource() .
                ' WHERE resource_id = "' . $this->id . '" AND deleted_at IS NULL'
            )
        ))->toArray();

        if ($results) {
            return $results[0]['total'];
        }

        return 0;
    }

    public function getBots()
    {
        $query = "SELECT count(id) AS total, bot FROM stats 
            WHERE bot IS NOT NULL AND resource_id = '" . $this->id . "' GROUP BY bot ORDER BY total DESC";
        $model = new Stats();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray();
    }

    public function getReferrers()
    {
        $query = "SELECT count(id) AS total, referer FROM stats WHERE referer IS NOT NULL 
            AND resource_id = '" . $this->id . "' GROUP BY referer ORDER BY total DESC";
        $model = new Stats();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray();
    }

    public function getStats($year = '')
    {
        if (empty($year)) {
            $year = date('Y');
        }

        $model = new Stats();
        $table = $model->getSource();

        $query = 'SELECT ';
        for ($iLoop = 1; $iLoop <= 12; $iLoop++) {
            $month = $iLoop;
            if ($month < 10) {
                $month = '0' . $month;
            }

            $query .= "(SELECT count(id) FROM $table WHERE created_at BETWEEN '" . $year . "-" .
                    $month . "-01' AND '" . $year . "-" .
                    $month . "-31' AND resource_id = '" . $this->id . "') AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_visitors,";

            $query .= "(SELECT count(*) FROM (SELECT count(id) FROM $table WHERE bot IS NULL AND 
                    created_at BETWEEN '" . $year . "-" . $month . "-01' AND '" . $year . "-" .
                    $month . "-31' AND resource_id = '" . $this->id . "' GROUP BY visitor) AS total) AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_unique,";

            $query .= "(SELECT count(id) FROM stats WHERE bot IS NOT NULL AND 
                    created_at BETWEEN '" . $year . "-" . $month . "-01' AND '" . $year . "-" .
                    $month . "-31' AND resource_id = '" . $this->id . "') AS " .
                    strtolower(date("F", strtotime(date('Y') . "-" . $month . "-01"))) . "_bot,";
        }

        $query .= "(SELECT count(id) FROM $table WHERE resource_id = '" . $this->id . "') AS total,";

        $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];

        $return = [];

        foreach ($results as $key => $result) {
            $splits = explode('_', $key);
            if (count($splits) == 1) {
                $return[$splits[0]] = $result;
            } else {
                if (empty($return[$splits[1]])) {
                    $return[$splits[1]] = [];
                }

                $return[$splits[1]][$splits[0]] = $result;
            }
        }

        return $return;
    }

    public function getStock()
    {
        return !empty($this->product) ? $this->product->stock : 0;
    }

    public function getRealUrl()
    {
        return !empty($this->canonical_url) ? $this->canonical_url : $this->url;
    }

    public function getVat()
    {
        $settings = (new Settings())->findFirst([
            'conditions' => 'id IS NOT NULL'
        ]);

        return !empty($this->product) ? $this->product->vat : $settings->vat;
    }

    public function inCarousel($file_id)
    {
        if (empty($this->carousel)) {
            return false;
        }

        foreach ($this->carousel as $file) {
            if ($file_id == $file->file_id) {
                return true;
            }
        }

        return false;
    }

    public function isLowStock()
    {
        if (!empty($this->product)) {
            if ($this->product->stock <= $this->product->low_stock) {
                return true;
            }
        }

        return false;
    }
}
