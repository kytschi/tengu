<?php

/**
 * Seo Campaigns controller.
 *
 * @package     Kytschi\Makabe\Controllers\SeoCampaignsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Makabe\Controllers;

use Google\Client as GoogleClient;
use Kytschi\Makabe\Controllers\PageScannerController;
use Kytschi\Makabe\Models\Campaigns;
use Kytschi\Makabe\Models\ScanPages;
use Kytschi\Makabe\Traits\SearchEngines;
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
use Kytschi\Tengu\Traits\Core\Queue;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Encryption\Security\Random;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use simplehtmldom\HtmlDocument;

class SeoCampaignsController extends ControllerBase
{
    use Files;
    use Form;
    use Logs;
    use Notes;
    use Pagination;
    use Queue;
    use SearchEngines;
    use Tags;

    public $access = [
        'administrator',
        'super-user',
        'seo-manager'
    ];

    public $global_url = '/seo-campaigns';
    public $resource = 'seo-campaign';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->mms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Creating an SEO campaign');

        return $this->view->partial(
            'makabe/seo_campaigns/add',
            [
                'search_engines' => $this->search_engines
            ]
        );
    }

    public function cloudSearch()
    {
        if (empty($_GET['search'])) {
            return [];
        }

        $settings = $this->getSettings();

        $client = new GoogleClient();
        $client->setAuthConfig(($this->di->getConfig())->application->appDir . '../' . $_ENV['GOOGLE_AUTH_JSON']);
        $client->setScopes(\Google\Service\CloudSearch::CLOUD_SEARCH);
        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
        $client->setAccessType('offline');

        try {
            $httpClient = $client->authorize();
            $response = $httpClient->post(
                'https://cloudsearch.googleapis.com/v1/query/search',
                [
                    'query' => urlencode($_GET['search'])
                ]
            );

            $body = json_decode($response->getBody()->getContents());
            if (empty($body)) {
                return [];
            }

            if (!empty($body->error)) {
                var_dump($body->error->message);
                die();
                return $body->error->message;
            }

            var_dump($body);
            die();
        } catch (\GuzzleHttp\Exception\ClientException $err) {
            $response = $e->getResponse();
            echo $response->getBody()->getContents();
        }
    }

    public function createCloudSearchDatasource($model)
    {
        $settings = $this->getSettings();
        $name = $this->createSlug($settings->name);
        $config = json_decode(
            file_get_contents(($this->di->getConfig())->application->appDir . '../' . $_ENV['GOOGLE_AUTH_JSON'])
        );

        $client = new GoogleClient();
        $client->setAuthConfig(($this->di->getConfig())->application->appDir . '../' . $_ENV['GOOGLE_AUTH_JSON']);
        $client->setScopes(\Google\Service\CloudSearch::CLOUD_SEARCH);

        try {
            $httpClient = $client->authorize();
            /*$response = $httpClient->post(
                'https://cloudsearch.googleapis.com/v1/settings/datasources',
                [
                    'displayName' => $name
                ]
            );*/

            $response = $httpClient->get(
                'https://cloudsearch.googleapis.com/v1/settings/searchapplications'
            );

            $body = json_decode($response->getBody()->getContents());
            if (empty($body)) {
                return [];
            }

            if (!empty($body->error)) {
                self::dump($body->error);
                var_dump($body->error->message);
                die();
                return $body->error->message;
            }

            var_dump($body);
            die();
        } catch (\GuzzleHttp\Exception\ClientException $err) {
            $response = $e->getResponse();
            echo $response->getBody()->getContents();
        }
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Campaigns())->findFirst([
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

            $this->saveFormDeleted('Campaign has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function deleteResultAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Campaigns())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        if (!file_exists(($this->di->getConfig())->application->dumpDir . $model->id . '-seo-campaign.html')) {
            return $this->notFound('File not found');
        }

        try {
            $this->deleteResult($model, urldecode($this->dispatcher->getParam('url')));
            
            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'SEO campaign search engine result deleted by ' . $this->getUserFullName()
            );

            $url = UrlHelper::backend($this->global_url . '/edit/' . $model->id);
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Campaign search engine result has been deleted');
            $this->redirect(rtrim($url, '/'));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    private function deleteResult($model, $delete)
    {
        $file = ($this->di->getConfig())->application->dumpDir . $model->id . '-seo-campaign.html';
        $html = new HtmlDocument();
        $html->load(file_get_contents($file));

        if (
            in_array(
                strtolower($model->search_engine->name),
                [
                    'google uk',
                    'google'
                ]
            )
        ) {
            foreach ($html->find('div[id^="RELATED_QUESTION_LINK"') as $element) {
                $element->remove();
            }

            foreach ($html->find('div[data-sokoban-container]') as $element) {
                $selected = false;

                $a_tag = $element->find('a');
                $url = ($a_tag) ? $a_tag[0]->href : '';
                if ($url == $delete) {
                    $a_tag[0]->href = 'DELETED';
                }
            }
        }
        $html->save($file);

        /*file_put_contents(
            $file,
            $html
        );*/
    }

    public function editAction()
    {
        $this->secure($this->access);

        $model = (new Campaigns())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle($model->name);

        return $this->view->partial(
            'makabe/seo_campaigns/edit',
            [
                'data' => $model,
                'search_engines' => $this->search_engines,
                'overview' => $this->getOverview($model),
                'search_results' => $this->searchPages($model)
            ]
        );
    }

    private function getOverview($model, $delete = null)
    {
        $overview = [];

        if (!file_exists($file = ($this->di->getConfig())->application->dumpDir . $model->id . '-seo-campaign.html')) {
            return $overview;
        }

        $html = new HtmlDocument();
        $html->load(file_get_contents($file));

        $pages = [];
        foreach ($model->scan_pages as $page) {
            $pages[] = $page->url;
        }

        if (
            in_array(
                strtolower($model->search_engine->name),
                [
                    'google uk',
                    'google'
                ]
            )
        ) {
            foreach ($html->find('div[id^="RELATED_QUESTION_LINK"') as $element) {
                $element->remove();
            }

            foreach ($html->find('div[data-sokoban-container]') as $element) {
                $selected = false;

                $url = ($element->find('a')) ? $element->find('a')[0]->href : '';

                $description = '';
                $description_tag = $element->find('[data-content-feature="1"]');
                if ($description_tag) {
                    $description = $description_tag[0]->find('div');
                    $description = isset($description[0]) ? $description[0]->plaintext : '';
                }

                if (empty($url) || empty($description)) {
                    continue;
                }

                if (in_array($url, $pages)) {
                    $selected = true;
                }

                $overview[] = [
                    'url' => $url,
                    'title' => ($element->find('h3')) ? $element->find('h3')[0]->plaintext : '',
                    'description' => $description,
                    'selected' => $selected
                ];
            }
        }

        return $overview;
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('SEO campaigns');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'name',
                'created_by'
            ],
            'name'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Campaigns::class)
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
            'makabe/seo_campaigns/index',
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

        $model = (new Campaigns())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $this->scan($model);

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Search engine has been re-scanned');
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

        $model = (new Campaigns())->findFirst([
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

            $this->saveFormUpdated('Campaign has been recovered');
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

            $model = $this->setData(new Campaigns());
            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to add the campaign',
                    $model->getMessages()
                );
            }

            if (!empty($_FILES['upload_html']['tmp_name'])) {
                file_put_contents(
                    ($this->di->getConfig())->application->dumpDir . $model->id . '-seo-campaign.html',
                    file_get_contents($_FILES['upload_html']['tmp_name'])
                );
                $model->search_engine_last_scanned = date('Y-m-d H:i:s');

                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to add the campaign',
                        $model->getMessages()
                    );
                }
            }

            $this->addTagsFromRequest($model->id, true);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            if (empty($_FILES['upload_html']['tmp_name'])) {
                $this->scan($model);
            }

            $this->saveFormSaved('Campaign has been saved');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/build'));
        } catch (Exception $err) {
            if (!empty($model)) {
                $model->delete();
            }
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    private function scan($model)
    {
        $source = shell_exec('chromium \
            --incognito \
            --proxy-auto-detect \
            --headless \
            --disable-gpu \
            --user-data-dir=~/.config/google-chrome/Default \
            --dump-dom ' . $model->search_engine_url);

        $html = new HtmlDocument();
        $html->load($source);

        if ($html->find('#recaptcha')) {
            throw new \Exception('Search engine blocking search');
        }

        $save_html = '';
        if (in_array(strtolower($model->search_engine->name), ['google uk'])) {
            foreach ($html->find('div[data-sokoban-container]') as $key => $element) {
                $save_html .= $element->save();
            }
        }

        if (empty($save_html)) {
            $this->saveFormError('Failed to scan using the search engine, no HTML');
            $this->addLog(
                $this->resource,
                $model->id,
                'error',
                'Failed to scan using the search engine, no HTML'
            );
        }

        file_put_contents(
            ($this->di->getConfig())->application->dumpDir . $model->id . '-seo-campaign.html',
            $save_html
        );

        $model->search_engine_last_scanned = date('Y-m-d H:i:s');

        if ($model->save() === false) {
            $this->addLog(
                $this->resource,
                $model->id,
                'error',
                'Failed to scan using the search engine',
                $model->getMessages()
            );
            $this->saveFormError('Failed to scan using the search engine');
        }

        $this->addLog(
            $this->resource,
            $model->id,
            'info',
            'Search engine scanned and downloaded by ' . $this->getUserFullName()
        );
    }

    public function searchAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Campaigns())->findFirst([
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
                file_get_contents(($this->di->getConfig())->application->dumpDir . $model->id . '-seo-campaign.html')
            );

            $tags = [];

            foreach ($html->find('text') as $tag) {
                if (strpos(strtolower($tag->plaintext), strtolower($_GET['seo_campaigns_search'])) !== false) {
                    $tags[] = $tag->plaintext;
                }
            }

            $this->setPageTitle($model->name);

            return $this->view->partial(
                'makabe/seo_campaigns/edit',
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

    public function searchPages($model)
    {
        if (empty($_GET['search_keyword'])) {
            return  [];
        }

        try {
            $tags = [];

            foreach ($model->scan_pages as $page) {
                if (
                    !file_exists(
                        $filename = ($this->di->getConfig())->application->dumpDir . $page->id . '-scan-page.html'
                    )
                ) {
                    continue;
                }

                $html = new HtmlDocument();
                $html->load(
                    file_get_contents($filename)
                );

                foreach ($html->find('text') as $tag) {
                    if (strpos(strtolower($tag->plaintext), strtolower($_GET['search_keyword'])) !== false) {
                        $tags[] = [
                            'id' => $page->id,
                            'tag' => $tag->tag,
                            'text' => $tag->plaintext,
                            'rank' => $page->rank,
                            'source' => $page->name .
                                '<br/><a href="' . $page->url . '" target="_blank">' . $page->url . '</a>'
                        ];
                    }
                }
            }

            return $tags;
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
        $model->search_engine_id = $_POST['search_engine_id'];
        $model->search_terms = $_POST['search_terms'];
        $model->status = !empty($_POST['status']) ? $this->isValidStatus($_POST['status']) : $this->default_status;

        return $model;
    }

    public function stats()
    {
        $table = (new Campaigns())->getSource();

        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE status = 'active') AS active,";
        $query .= "(SELECT count(id) FROM $table WHERE deleted_at IS NOT NULL) AS deleted,";

        $model = new Campaigns();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Campaigns())->findFirst([
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

            if (!empty($_FILES['upload_html']['tmp_name'])) {
                $this
                    ->modelsManager
                    ->executeQuery(
                        'UPDATE ' .
                        ScanPages::class .
                        ' SET deleted_at=NOW(), deleted_by=:deleted_by: WHERE campaign_id = :campaign_id:',
                        [
                            'deleted_by' => self::getUserId(),
                            'campaign_id' => $model->id
                        ]
                    );

                file_put_contents(
                    ($this->di->getConfig())->application->dumpDir . $model->id . '-seo-campaign.html',
                    file_get_contents($_FILES['upload_html']['tmp_name'])
                );

                $this->addLog(
                    $this->resource,
                    $model->id,
                    'info',
                    'HTML manually uploaded by ' . $this->getUserFullName()
                );

                $model->search_engine_last_scanned = date('Y-m-d H:i:s');
            }

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the campaign',
                    $model->getMessages()
                );
            }

            if (!empty($_POST['site_url'])) {
                $table = (new ScanPages())->getSource();
                $params = [
                    ':campaign_id' => $model->id,
                    ':created_at' => date('Y-m-d H:i:s'),
                    ':created_by' => self::getUserId(),
                    ':updated_at' => date('Y-m-d H:i:s'),
                    ':updated_by' => self::getUserId()
                ];

                $this
                    ->modelsManager
                    ->executeQuery(
                        'UPDATE ' .
                        ScanPages::class .
                        ' SET deleted_at=NOW(), deleted_by=:deleted_by: WHERE campaign_id = :campaign_id:',
                        [
                            'deleted_by' => self::getUserId(),
                            'campaign_id' => $model->id
                        ]
                    );

                $query = '';

                foreach ($_POST['site_url'] as $key => $string) {
                    $splits = explode('|', $string);
                    $url = $splits[0];
                    $rank = $splits[1];
                    unset($splits[0]);
                    unset($splits[1]);
                    $title = implode('|', $splits);

                    $query .=
                        'INSERT INTO ' . $table . '
                        (id, campaign_id, rank, name, url, created_at, created_by, updated_at, updated_by)
                            SELECT
                                :id_' . $key . ',
                                :campaign_id,
                                :rank_' . $key . ',
                                :name_' . $key . ',
                                :url_' . $key . ',
                                :created_at,
                                :created_by,
                                :updated_at,
                                :updated_by
                            FROM DUAL
                            WHERE NOT EXISTS
                            (
                                SELECT
                                    id,
                                    campaign_id,
                                    rank,
                                    name,
                                    url,
                                    created_at,
                                    created_by,
                                    updated_at,
                                    updated_by
                                FROM ' . $table . '
                                WHERE
                                    campaign_id=:campaign_id AND url=:url_' . $key . ' AND rank=:rank_' . $key . '
                            );
                        UPDATE ' . $table . ' SET deleted_at=NULL, deleted_by=NULL
                        WHERE campaign_id=:campaign_id AND url=:url_' . $key . ' AND rank=:rank_' . $key . ';';

                    $params = array_merge(
                        $params,
                        [
                            ':id_' . $key => (new Random())->uuid(),
                            ':name_' . $key => $title,
                            ':url_' . $key => $url,
                            ':rank_' . $key => $rank
                        ]
                    );
                }

                $this->db->query($query, $params);

                $this->addJobToQueue(
                    $this->resource,
                    $model->id,
                    'Kytschi\\Makabe\\Jobs\\CampaignScanPages'
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

            $this->saveFormUpdated('Campaign has been updated');

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

        $validation->add(
            'search_engine_id',
            new PresenceOf(
                [
                    'message' => 'The search engine is required',
                ]
            )
        );

        $validation->add(
            'search_terms',
            new PresenceOf(
                [
                    'message' => 'The search terms are required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
