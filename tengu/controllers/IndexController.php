<?php

/**
 * Index controller.
 *
 * @package     Kytschi\Tengu\Controllers\IndexController
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

use Kytschi\Makabe\Controllers\KeywordsController;
use Kytschi\Makabe\Models\SpinContents;
use Kytschi\Makabe\Models\SpinContent;
use Kytschi\Makabe\Models\SpunContent;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Controllers\Core\DashboardsController;
use Kytschi\Tengu\Exceptions\TemplateException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Pages;
use Kytschi\Tengu\Traits\Core\Api;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Tengu\Traits\Core\Validation;
use Kytschi\Tengu\Traits\Website\OldUrls;
use Kytschi\Tengu\Traits\Website\Shortcodes;
use Kytschi\Tengu\Traits\Website\Stats;
use Phalcon\Paginator\Adapter\NativeArray;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Tag;

class IndexController extends ControllerBase
{
    use Api;
    use Form;
    use Logs;
    use OldUrls;
    use Pagination;
    use Shortcodes;
    use Stats;
    use Tags;

    private $add_stat = true;

    public function fallbackAction()
    {
        if (TENGU_API) {
            $this->apiResponse('Invalid endpoint', null, 404);
        }

        if (isset($_GET['tengu-stat-ignore'])) {
            if ($_GET['tengu-stat-ignore'] == 'true') {
                $this->add_stat = false;
            }
        }

        $path = parse_url($_SERVER['REQUEST_URI']);
        if (!empty($path['path'])) {
            $url = rtrim($path['path'], '/');
        } else {
            $url = '/';
        }
        if (!$url) {
            $url = '/';
        }

        if (TENGU_BACKEND) {
            $app_url = rtrim($_ENV['APP_TENGU_URL'], '/');
            if (!$app_url) {
                $app_url = '/';
            }

            if ($url == $app_url) {
                return (new DashboardsController())->homeAction();
            }
        }

        Tag::setDefault('robots', $this->tengu->settings->robots);
        $page = Pages::findFirst(
            [
                'conditions' => 'deleted_at IS NULL AND 
                    (url = :url: OR canonical_url = :canonical_url:) AND 
                    status = "active"',
                'bind' => [
                    'url' => $url,
                    'canonical_url' => $url
                ]
            ]
        );

        if (empty($page)) {
            if ($page = $this->checkOldUrl($url, true, true)) {
                $this->redirect($page->url);
            } else {
                $this->setPageTitle('Page not found');

                Tag::setDefault('page_updated', date('Y-m-d H:i:s'));
                Tag::setDefault('meta_description', $this->tengu->settings->meta_description);
                Tag::setDefault('meta_keywords', $this->tagsToString($this->tengu->settings->tags));
                Tag::setDefault(
                    'meta_author',
                    !empty($this->tengu->settings->meta_author) ?
                        $this->tengu->settings->meta_author :
                        $this->tengu->settings->name
                );

                return $this->notFound('Page not found');
            }
        }

        if (empty($page->template)) {
            throw new TemplateException('Template not found', null, 404);
        }

        $this->setPageTitle($page->name);
        $this->setPageTags($page);
        $page->content = $this->parseShortcodes($page->content);

        if (isset($_GET['search'])) {
            return $this->frontSearch($page);
        }

        if ($page->spinnable && !empty(($this->di->getConfig())->makabe)) {
            $page = $this->sortSpin($page);
        }

        if ($this->add_stat && $_ENV['APP_ENV'] != 'local') {
            $this->addStat(
                $page->id,
                'page'
            );
        }

        try {
            return $this->view->partial(
                $page->template->file,
                [
                    'page' => $page
                ]
            );
        } catch (\Exception $err) {
            throw new TemplateException($err->getMessage());
        }
    }

    private function frontSearch($page)
    {
        $this->clearFormData();
        $this->savePagination();

        $this->setIndexDefaults(
            [
                'name',
                'url',
                'status'
            ],
            'name'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Pages::class)
            ->orderBy('name asc')
            ->where('deleted_at IS NULL AND status="active" AND searchable=1')
            ->andWhere('name LIKE :name: OR url LIKE :url: OR search_tags LIKE :search_tags:')
            ->setBindParams(
                [
                    'name' => '%' . $this->search . '%',
                    'url' => '%' . $this->search . '%',
                    'search_tags' => '%,' . $this->search . ',%'
                ]
            );

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        $this->addSearchStat($this->search);

        return $this->view->partial(
            $page->template->file,
            [
                'page' => $page,
                'results' => $paginator->paginate()
            ]
        );
    }

    public function humansAction()
    {
        header("Content-Type: text/plain");
        if (!empty($this->tengu->settings->humans_txt)) {
            echo $this->tengu->settings->humans_txt;
        } else {
            echo "/* TEAM */\n";
            echo "Your title: " . $this->tengu->settings->name . "\n";
            echo "Site: " . $this->tengu->settings->name . "\n";
            if (!empty($this->tengu->settings->address)) {
                echo "Location: " . str_replace("\n", ", ", $this->tengu->settings->address) . "\n";
            }
            echo "/* SITE */\n";
            if (!empty($this->tengu->settings->last_update)) {
                echo "Last update: " . date('Y/m/d', strtotime($this->tengu->settings->last_update)) . "\n";
            }
            echo "Doctype: HTML5\n";
        }
        die();
    }

    public function robotsAction()
    {
        header("Content-Type: text/plain");
        echo $this->tengu->settings->robots_txt;
        echo "\nSitemap: " . ($this->di->getConfig())->application->appUrl . '/sitemap.xml';
        die();
    }

    public function rssAction()
    {
        $pages = (new Pages())->find([
            'conditions' => 'deleted_at IS NULL AND sitemap = 1 AND status="active"',
            'order' => 'created_at DESC'
        ]);

        $url = ($this->di->getConfig())->application->appUrl;

        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<rss version="2.0">' . "\n";
        echo '<channel>' . "\n";
        echo '<title>' . $this->tengu->settings->name . '</title>' . "\n";
        echo '<link>' . $url . '</link>' . "\n";
        echo '<description>' . $this->tengu->settings->meta_description . '</description>' . "\n";
        foreach ($pages as $page) {
            echo '<item>' . "\n";
            echo '<title>' . htmlspecialchars($page->name, ENT_XML1) . '</title>' . "\n";
            echo '<link>' .  $url . $page->url . '</link>' . "\n";
            echo '<guid>' .  $url . $page->url . '</guid>' . "\n";
            echo '<pubDate>' . (new \DateTime($page->created_at))->format(\DateTime::ATOM) . '</pubDate>' . "\n";
            if ($page->summary) {
                echo '<description>' .  $page->summary . '</description>' . "\n";
            } elseif ($page->meta_description) {
                echo '<description>' .  $page->meta_description . '</description>' . "\n";
            }
            echo '</item>' . "\n";
        }
        echo '</channel>' . "\n";
        echo '</rss>' . "\n";
        die();
    }

    public function searchAction()
    {
        $this->secure();
        $this->savePagination();
        $this->setPageTitle('Search');

        if (empty($this->search)) {
            $data = null;
        } else {
            $params[':search'] = "%" . $this->search . "%";
            //Pages
            $query = "SELECT
                    id,
                    CASE type
                        WHEN 'blog-post' THEN '/cms/blog-posts'
                        WHEN 'portfolio' THEN '/cms/portfolio'
                        ELSE '/cms/pages'
                    END AS tengu_url,
                    type,
                    name,
                    content,
                    status,
                    updated_at,
                    CASE type
                        WHEN 'blog-post' THEN '" . urlencode($this->setIcon('blog-post')) . "'
                        WHEN 'portfolio' THEN '" . urlencode($this->setIcon('portfolio')) . "'
                        ELSE '" . urlencode($this->setIcon('page')) . "'
                    END AS icon
                FROM pages 
                WHERE name LIKE :search";
            $query .= " UNION ";
            //Menu
            $query .= "SELECT
                    id,
                    '/cms/menu' AS tengu_url,
                    'menu' AS type,
                    name,
                    '' AS content,
                    status, 
                    updated_at,
                    '" . urlencode($this->setIcon('menu')) . "' AS icon
                FROM menu 
                WHERE name LIKE :search";
            $query .= " UNION ";
            //Themes
            $query .= "SELECT
                    id,
                    '/cms/themes' AS tengu_url,
                    'theme' AS type,
                    name,
                    '' AS content,
                    status,
                    updated_at,
                    '" . urlencode($this->setIcon('menu')) . "' AS icon
                FROM themes 
                WHERE name LIKE :search";
            $query .= " UNION ";
            //Users
            $query .= "SELECT
                        id,
                        '/users' AS tengu_url,
                        'user' AS type,
                        CONCAT(first_name, ' ', last_name) AS name,
                        '' AS content,
                        status,
                        updated_at,
                        '" . urlencode($this->setIcon('menu')) . "' AS icon
                    FROM users 
                    WHERE first_name LIKE :search OR last_name LIKE :search";
            $query .= " UNION ";
            //Groups
            $query .= "SELECT
                        id,
                        '/groups' AS tengu_url,
                        'group' AS type,
                        name,
                        '' AS content,
                        status, 
                        updated_at,
                        '" . urlencode($this->setIcon('menu')) . "' AS icon
                    FROM groups 
                    WHERE name LIKE :search";
            //Templates
            $query .= " UNION ";
            $query .= "SELECT 
                        id,
                        '/templates' AS tengu_url,
                        'template' AS type,
                        name,
                        '' AS content,
                        IF(deleted_at IS NULL, 'active', 'deleted') AS status,
                        updated_at,
                        '" . urlencode($this->setIcon('menu')) . "' AS icon
                    FROM templates
                    WHERE name LIKE :search";

            foreach (($this->di->getConfig())->apps as $app => $status) {
                if (
                    $status &&
                    class_exists($class = 'Kytschi\\' . ucwords($app) . '\\Controllers\\SearchController')
                ) {
                    $controller = new $class();
                    if (method_exists($controller, 'searchQuery')) {
                        $query = $controller->searchQuery($query);
                    }
                }
            }

            $pages = new Pages();
            $results = (new \Phalcon\Mvc\Model\Resultset\Simple(
                null,
                $pages,
                $pages
                    ->getReadConnection()
                    ->query(
                        rtrim($query, ','),
                        $params
                    )
            ))->toArray();

            $paginator = new NativeArray(
                [
                    "data" => $results,
                    "limit" => $this->perPage,
                    "page" => $this->page,
                ]
            );

            $data = $paginator->paginate();
        }

        return $this->view->partial(
            'core/dashboards/search',
            [
                'data' => $data
            ]
        );
    }

    private function setIcon($type)
    {
        switch ($type) {
            case 'blog-post':
                return '<span class="mr-4" title="I\'m a blog post"
                data-toggle="tooltip" data-trigger="hover" data-dismiss="click" data-placement="top">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                <path d="M4.5 12.5A.5.5 0 0 1 5 12h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 10h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm1.639-3.708 1.33.886 1.854-1.855a.25.25 0 0 1 .289-.047l1.888.974V8.5a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V8s1.54-1.274 1.639-1.208zM6.25 6a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5z"/>
                </svg>
                </span>';
            break;
            case 'portfolio':
                return '<span class="mr-4" title="I\'m a blog post"
                data-toggle="tooltip" data-trigger="hover" data-dismiss="click" data-placement="top">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                <path d="M4.5 12.5A.5.5 0 0 1 5 12h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 10h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm1.639-3.708 1.33.886 1.854-1.855a.25.25 0 0 1 .289-.047l1.888.974V8.5a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V8s1.54-1.274 1.639-1.208zM6.25 6a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5z"/>
                </svg>
                </span>';
            break;
            case 'page':
                return '<span class="mr-4" title="I\'m a blog post"
                data-toggle="tooltip" data-trigger="hover" data-dismiss="click" data-placement="top">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 16">
                <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                <path d="M4.5 12.5A.5.5 0 0 1 5 12h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 10h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm1.639-3.708 1.33.886 1.854-1.855a.25.25 0 0 1 .289-.047l1.888.974V8.5a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V8s1.54-1.274 1.639-1.208zM6.25 6a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5z"/>
                </svg>
                </span>';
            break;
            default:
                return '';
                break;
        }
    }

    public function sitemapAction($return = false)
    {
        $pages = (new Pages())->find([
            'conditions' => 'deleted_at IS NULL AND sitemap = 1 AND status="active"',
            'order' => 'name'
        ]);

        if ($return) {
            return $pages;
        }

        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        if ($pages->count()) {
            $url = ($this->di->getConfig())->application->appUrl;
            foreach ($pages as $page) {
                if (
                    strpos($page->url, 'http://') !== false ||
                    strpos($page->url, 'https://') !== false ||
                    strpos($page->url, 'ftp://') !== false ||
                    strpos($page->url, 'sftp://') !== false
                ) {
                    continue;
                }

                echo '<url>';
                echo '<loc>' .  $url . $page->url . '</loc>';
                echo '<lastmod>' . (new \DateTime($page->updated_at))->format(\DateTime::ATOM) . '</lastmod>';
                echo '</url>';
            }
        }
        echo '</urlset>';
        die();
    }

    private function sortSpin($page)
    {
        $first_time = true;

        if (!empty($page->current_spun_content->used_at)) {
            $last_spun = new \DateTime($page->current_spun_content->used_at);
            $today = new \DateTime();
            $diff = $last_spun->diff($today);
            $days = intval($diff->format('%a'));
            $first_time = false;
            $sort = $page->current_spun_content->sort + 1;
            if ($sort > $page->spun_count) {
                $sort = 1;
            }
        } else {
            $days = intval(($this->di->getConfig())->makabe->spin_days);
            $sort = 1;
        }

        if (($this->di->getConfig())->makabe->spin_days - $days < 0) {
            $table = (new SpunContent())->getSource();
            $this->db->query(
                'UPDATE ' . $table . ' SET status="pending" WHERE resource_id = :resource_id',
                [
                    ':resource_id' => $page->id
                ]
            );
            if (!$first_time) {
                $spun = (new SpunContent())->findFirst([
                    'conditions' => 'deleted_at IS NULL AND resource_id = :resource_id: AND sort = :sort:',
                    'bind' => [
                        'resource_id' => $page->id,
                        'sort' => $sort
                    ]
                ]);
            }

            if (empty($spun)) {
                $spun = (new SpunContent())->findFirst([
                    'conditions' => 'deleted_at IS NULL AND resource_id = :resource_id: AND sort = :sort:',
                    'bind' => [
                        'resource_id' => $page->id,
                        'sort' => 1
                    ]
                ]);
            }

            if (empty($spun)) {
                $page->spinnable = 0;
                if ($page->update() === false) {
                    throw new SaveException(
                        'Failed to update the page',
                        $page->getMessages()
                    );
                }

                $this->addLog(
                    $page->type,
                    $page->id,
                    'warning',
                    'Spinning disabled by the system due to no suitable spun content'
                );
                return $page;
            }

            $this->pageSpin($page, $spun);
        } elseif (!empty($page->current_spun_content)) {
            $spun = $page->current_spun_content;
            if (empty($page->last_spun)) {
                $page->content = $spun->content;
                $page->last_spun = $spun->used_at;
                if ($page->update() === false) {
                    throw new SaveException(
                        'Failed to update the page',
                        $page->getMessages()
                    );
                }
            }
        } elseif (empty($spun)) {
            $table = (new SpunContent())->getSource();
            $this->db->query(
                'UPDATE ' . $table . ' SET status="pending" WHERE resource_id = :resource_id',
                [
                    ':resource_id' => $page->id
                ]
            );
            $spun = (new SpunContent())->findFirst([
                'conditions' => 'deleted_at IS NULL AND resource_id = :resource_id: AND sort = :sort:',
                'bind' => [
                    'resource_id' => $page->id,
                    'sort' => 1
                ]
            ]);
            $this->pageSpin($page, $spun);
        }

        if ($this->add_stat) {
            $this->addStat(
                $spun->id,
                'spun-content',
                $spun->spin_content_id
            );

            if (!empty($spun->spin_content)) {
                if (!empty($spun->spin_content->campaign)) {
                    $this->addStat(
                        $spun->spin_content->campaign->id,
                        $spun->spin_content->campaign->type . '-campaign',
                        $spun->spin_content_id
                    );
                }
            }

            (new KeywordsController())->addKeywordStat($spun);
        }

        return $page;
    }

    private function pageSpin($page, $spun)
    {
        $spun->status = 'live';
        $spun->used_at = date('Y-m-d H:i:s');
        if ($spun->update() === false) {
            throw new SaveException(
                "Failed to update the page's spun content",
                $spun->getMessages()
            );
        }

        $page->content = $spun->content;
        if (!empty($spun->spin_content->name)) {
            $page->name = $spun->spin_content->name;
        }
        $page->spins += 1;
        $page->last_spun = $spun->used_at;
        if ($page->update() === false) {
            throw new SaveException(
                'Failed to update the page',
                $page->getMessages()
            );
        }

        $this->addLog(
            $page->type,
            $page->id,
            'info',
            'Fresh spun content automatically used by the system'
        );
    }
}
