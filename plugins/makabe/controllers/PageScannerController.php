<?php

/**
 * Page Scanner controller.
 *
 * @package     Kytschi\Makabe\Controllers\PageScannerController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
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

namespace Kytschi\Makabe\Controllers;

use Google\Client as GoogleClient;
use Google\Service\Drive as GDrive;
use Google\Service\Drive\DriveFile;
use Kytschi\Makabe\Controllers\WordsController;
use Kytschi\Makabe\Exceptions\ParseException;
use Kytschi\Makabe\Models\ScanPages;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use simplehtmldom\HtmlDocument;

class PageScannerController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Notes;
    use Pagination;
    use Tags;

    public $access = [
        'administrator',
        'super-user',
        'seo-manager'
    ];

    public $global_url = '/page-scanner';
    public $resource = 'page-scanner';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->mms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Adding a page to scan');

        return $this->view->partial(
            'makabe/page_scanner/add'
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new ScanPages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->softDelete();

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Scan page has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function editAction()
    {
        $this->secure($this->access);

        $model = (new ScanPages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle($model->name);

        $error = null;
        $overview = null;

        try {
            $overview = $this->getOverview($model);
        } catch (ParseException $err) {
            $error = $err->getMessage();
        }

        return $this->view->partial(
            'makabe/page_scanner/edit',
            [
                'data' => $model,
                'overview' => $overview,
                'error' => $error
            ]
        );
    }

    public function getOverview($model)
    {
        $overview = [];

        if (!file_exists($file = ($this->di->getConfig())->application->dumpDir . $model->id . '-scan-page.html')) {
            return $overview;
        }

        $html = new HtmlDocument();
        @$html->load(file_get_contents($file));

        if (empty($html->find('html'))) {
            throw new ParseException('Failed to read the HTML correctly');
        }

        $overview['h_tags'] = [];
        $overview['meta'] = [];
        $overview['text'] = [];

        foreach ($html->find('title') as $tag) {
            $overview['title'] = $tag->plaintext;
            break;
        }

        foreach ($html->find('h1, h2, h3, h4, h5, h6') as $tag) {
            if (!empty($tag->plaintext)) {
                $overview['h_tags'][] = [
                    'tag' => $tag->tag,
                    'text' => $tag->plaintext
                ];
            }
        }

        foreach ($html->find('meta') as $tag) {
            if (
                isset($tag->name) &&
                isset($tag->content) &&
                in_array(strtolower($tag->name), ['description', 'keywords'])
            ) {
                $overview['meta'][] = [
                    'type' => strtolower($tag->name),
                    'content' => $tag->content
                ];
            }
        }

        foreach ($html->find('text') as $tag) {
            $text = strip_tags($tag->plaintext);

            if (
                in_array($tag->tag, ['script', 'style', 'br']) ||
                empty($text)
            ) {
                continue;
            }

            switch ($tag->tag) {
                case 'a':
                    if (isset($tag->href)) {
                        $overview['text'][] = [
                            'tag' => $tag->tag,
                            'extra' => $tag->href,
                            'text' => $tag->plaintext
                        ];
                    } else {
                        $overview['text'][] = [
                            'tag' => $tag->tag,
                            'extra' => 'No link',
                            'text' => $tag->plaintext
                        ];
                    }
                    break;
                default:
                    $overview['text'][] = [
                        'tag' => $tag->tag,
                        'extra' => '',
                        'text' => $tag->plaintext
                    ];
                    break;
            }
        }

        return $overview;
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Pages to scan');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'name',
                'url',
                'created_by'
            ],
            'name'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(ScanPages::class)
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'name' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('name LIKE :name:');
        }

        if (!empty($this->filters)) {
            $iLoop = 1;
            foreach ($this->filters as $filter => $value) {
                if (empty($value)) {
                    continue;
                }

                switch ($filter) {
                    case 'status':
                        $builder->andWhere('status LIKE :status_' . $iLoop . ':');
                        $params['status_' . $iLoop] = $value;
                        $iLoop++;
                        break;
                }
            }
        }

        $builder->setBindParams($params);

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        return $this->view->partial(
            'makabe/page_scanner/index',
            [
                'data' => $paginator->paginate(),
                //'stats' => $this->stats()
            ]
        );
    }

    public function rescanAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new ScanPages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model = $this->scan($model, true);

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            if ($model->scan_status == 'scanned') {
                $this->saveFormUpdated('Scan page has been scanned');
            } else {
                $this->saveFormError('Failed to scan the page');
            }
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new ScanPages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->recover();

            $this->addLog(
                $this->resource,
                $model->id,
                'warning',
                'Recovered by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Scan page has been recovered');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function saveAction()
    {
        $this->secure($this->access);

        try {
            $this->validate();

            $model = $this->setData(new ScanPages());
            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to add the page',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            if (!empty($_FILES['upload_html']['tmp_name'])) {
                $source = file_get_contents($_FILES['upload_html']['tmp_name']);

                file_put_contents(
                    ($this->di->getConfig())->application->dumpDir . $model->id . '-scan-page.html',
                    $source
                );

                $this->addLog(
                    $this->resource,
                    $model->id,
                    'info',
                    'HTML manually uploaded by ' . $this->getUserFullName()
                );

                (new WordsController())->count(
                    $source,
                    $this->resource,
                    $model->id,
                    $model->campaign_id
                );

                $this->uploadToGDrive($model);

                $model->scan_status = 'scanned';
                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the page',
                        $model->getMessages()
                    );
                }
            } else {
                $this->scan($model, true);
            }

            $this->saveFormSaved('Scan page has been saved');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/build'));
        } catch (Exception $err) {
            if (!empty($model)) {
                $model->delete();
            }

            if (!empty($board)) {
                $board->delete();
            }

            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function scan($model, $rescan = false)
    {
        if (
            file_exists(($this->di->getConfig())->application->dumpDir . $model->id . '-scan-page.html') &&
            !$rescan
        ) {
            return $model;
        }

        $html = @file_get_contents($model->url);
        if (!empty($html)) {
            @file_put_contents(($this->di->getConfig())->application->dumpDir . $model->id . '-scan-page.html', $html);
            $model->scan_status = 'scanned';

            //$this->uploadToGDrive($model);
        } else {
            $model->scan_status = 'failed';
        }

        $model->last_scanned = date('Y-m-d H:i:s');
        if ($model->update() === false) {
            throw new SaveException(
                'Failed to update the page scan',
                $model->getMessages()
            );
        }

        (new WordsController())->count(
            $html,
            $this->resource,
            $model->id,
            $model->campaign_id
        );

        $this->addLog(
            $this->resource,
            $model->id,
            'info',
            'Page scanned and downloaded by ' . $this->getUserFullName()
        );

        return $model;
    }

    public function searchAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new ScanPages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $html = new HtmlDocument();
            $html->load(
                @file_get_contents(($this->di->getConfig())->application->dumpDir . $model->id . '-scan-page.html')
            );

            $tags = [];

            foreach ($html->find('text') as $tag) {
                if (strpos(strtolower($tag->plaintext), strtolower($_GET['page_scanner_search'])) !== false) {
                    $tags[] = [
                        'tag' => $tag->tag,
                        'extra' => '',
                        'text' => $tag->plaintext
                    ];
                }
            }

            $this->setPageTitle($model->name);

            return $this->view->partial(
                'makabe/page_scanner/edit',
                [
                    'data' => $model,
                    'overview' => $this->getOverview($model),
                    'search_results' => $tags
                ]
            );
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function setData($model)
    {
        $model->name = $_POST['name'];
        $model->url = $_POST['url'];
        $model->status = !empty($_POST['status']) ? $this->isValidStatus($_POST['status']) : $this->default_status;

        return $model;
    }

    public function stats()
    {
        $table = (new ScanPages())->getSource();

        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE status = 'active') AS active,";
        $query .= "(SELECT count(id) FROM $table WHERE deleted_at IS NOT NULL) AS deleted,";

        $model = new ScanPages();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new ScanPages())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $this->validate();

            $model = $this->setData($model);
            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the page',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            if (!empty($_FILES['upload_html']['tmp_name'])) {
                if (file_exists(($this->di->getConfig())->application->dumpDir . $model->id . '-scan-page.html')) {
                    unlink(($this->di->getConfig())->application->dumpDir . $model->id . '-scan-page.html');
                }
                
                $source = @file_get_contents($_FILES['upload_html']['tmp_name']);

                file_put_contents(
                    ($this->di->getConfig())->application->dumpDir . $model->id . '-scan-page.html',
                    $source
                );

                $this->addLog(
                    $this->resource,
                    $model->id,
                    'info',
                    'HTML manually uploaded by ' . $this->getUserFullName()
                );

                (new WordsController())->count(
                    $source,
                    $this->resource,
                    $model->id,
                    $model->campaign_id
                );

                $this->uploadToGDrive($model);

                $model->scan_status = 'scanned';
                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the page',
                        $model->getMessages()
                    );
                }
            }

            $this->saveFormUpdated('Scan page has been updated');

            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    private function uploadToGDrive($model)
    {
        if (!$_ENV['GOOGLE_UPLOAD'] || $_ENV['GOOGLE_UPLOAD'] == 'false') {
            return;
        }

        $html = @file_get_contents(($this->di->getConfig())->application->dumpDir . $model->id . '-scan-page.html');
        if (empty($html)) {
            return;
        }

        $client = new GoogleClient();
        $client->setAuthConfig(($this->di->getConfig())->application->appDir . '../' . $_ENV['GOOGLE_AUTH_JSON']);
        $client->setScopes(GDrive::DRIVE);
        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
        $client->setAccessType('offline');

        $service = new GDrive($client);

        $shared_drive_id = '0AKWbvmjP2cT7Uk9PVA';

        $optParams = array(
            'corpora' => 'drive',
            'driveId' => $shared_drive_id,
            'pageSize' => 10,
            'includeItemsFromAllDrives' => true,
            'supportsAllDrives' => true,
            'fields' => 'nextPageToken, files(id, name)'
        );

        $results = $service->files->listFiles($optParams);

        $settings = $this->getSettings();

        $file_id = '';
        $site_folder_id = '';
        $seo_folder_id = '';
        foreach ($results->getFiles() as $file) {
            if ($file->getName() == $this->createSlug($settings->name)) {
                $site_folder_id = $file->getId();
                continue;
            }

            if ($file->getName() == 'seo') {
                $seo_folder_id = $file->getId();
                continue;
            }

            if ($file->getName() == $model->id . '-scan-page.html' && !$file->getTrashed()) {
                $file_id = $file->getId();
                continue;
            }
        }

        if (empty($seo_folder_id)) {
            throw new SaveException('Failed to save as SEO remote drive is missing');
        }

        if (empty($site_folder_id)) {
            $file = new DriveFile();
            $file->setName($this->createSlug($settings->name));
            $file->setMimeType('application/vnd.google-apps.folder');
            $file->setParents([$seo_folder_id]);

            $folder = $service->files->create(
                $file,
                [
                    'supportsAllDrives' => true,
                    'data' => $html,
                    'mimeType' => 'text/html'
                ]
            );
            $site_folder_id = $folder->getId();
        }

        if (empty($site_folder_id)) {
            throw new SaveException('Failed to save as SEO remote site drive is missing');
        }

        $file = new DriveFile();
        $file->setName($model->id . '-scan-page.html');
        $file->setParents([$site_folder_id]);

        if (empty($file_id)) {
            $created = $service->files->create(
                $file,
                [
                    'supportsAllDrives' => true,
                    'data' => $html,
                    'mimeType' => 'text/html'
                ]
            );
        } else {
            $empty = new DriveFile();

            $service->files->update(
                $file_id,
                $empty,
                [
                    'supportsAllDrives' => true,
                    'data' => $html,
                    'mimeType' => 'text/html'
                ]
            );
        }
    }

    public function validate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            'name',
            new PresenceOf(
                [
                    'message' => 'The name is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
