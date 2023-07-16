<?php

/**
 * Words controller.
 *
 * @package     Kytschi\Makabe\Controllers\WordsController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Makabe\Controllers;

use Kytschi\Makabe\Models\Words;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Logs as LogsModel;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Encryption\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use simplehtmldom\HtmlDocument;

class WordsController extends ControllerBase
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

    public $global_url = '/words';
    public $resource = 'word';

    private $params = [];
    private $query = '';

    private $ignore_words = [
        'the', 'in', 'of', 'as', 'its', 'it', 'was', 'not', 'by', 'and', 'did', 'to', 'or', 'for', 'is', 'until',
        'from', 'are', 'these', 'with', 'a', 'about', '-', '=', '+', '&', 'us', '–', '“', '[', ']', '[ ]', '[]', '|',
        '✓', '|', 'an', '/', 'at', 'on', 'do', 'be', 'he', 'she'
    ];

    private $clean_chars = [
        '?', '!', '"', '“', '|', "\n", "\n\r", "\r"
    ];

    private $delete_count = 2;

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->mms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Adding a word');

        return $this->view->partial(
            'makabe/page_scanner/exclude_words/add'
        );
    }

    public function count(
        $source,
        $resource,
        $resource_id,
        $campaign_id = '',
        $type = 'html'
    ) {
        if (empty($source)) {
            return;
        }

        $table = (new Words())->getSource();

        $this->params = [
            ':resource_id' => $resource_id,
            ':resource' => $resource,
            ':campaign_id' => $campaign_id,
            ':created_at' => date('Y-m-d H:i:s'),
            ':created_by' => self::getUserId(),
            ':updated_at' => date('Y-m-d H:i:s'),
            ':updated_by' => self::getUserId()
        ];

        $this
            ->modelsManager
            ->executeQuery(
                'UPDATE ' .
                Words::class .
                ' SET deleted_at=NOW(), deleted_by=:deleted_by: 
                WHERE resource_id = :resource_id: AND resource = :resource:',
                [
                    'deleted_by' => self::getUserId(),
                    'resource_id' => $resource_id,
                    'resource' => $resource
                ]
            );

        $this->query = '';

        if ($type == 'html') {
            $html = new HtmlDocument();
            $html->load($source);

            $content = '';

            foreach ($html->find('title') as $tag) {
                if (!empty(strip_tags($tag->plaintext))) {
                    $content .= $tag->plaintext . ' ';
                }
            }

            foreach ($html->find('h1, h2, h3, h4, h5, h6') as $tag) {
                if (!empty(strip_tags($tag->plaintext))) {
                    $content .= $tag->plaintext . ' ';
                }
            }

            foreach ($html->find('meta') as $tag) {
                if (
                    isset($tag->name) &&
                    isset($tag->content) &&
                    in_array(strtolower($tag->name), ['description', 'keywords'])
                ) {
                    if (!empty(strip_tags($tag->content))) {
                        $content .= $tag->content . ' ';
                    }
                }
            }

            foreach ($html->find('text') as $tag) {
                if (
                    in_array($tag->tag, ['script', 'style', 'br']) ||
                    empty($text)
                ) {
                    continue;
                }

                $content .= strip_tags($tag->plaintext) . ' ';
            }

            $content = str_replace($this->clean_chars, '', $content);

            $this->buildQueries($table, strtolower($content));
        }

        if ($this->query) {
            $this->db->query($this->query, $this->params);
        }

        $this->db->query(
            "DELETE FROM $table WHERE campaign_id=:campaign_id AND word_count <= :word_count",
            [
                ':campaign_id' => $campaign_id,
                ':word_count' => $this->delete_count
            ]
        );
    }

    public function countByWord($word)
    {
        $query = $this
            ->modelsManager
            ->createQuery('
            SELECT 
                SUM(word_count) AS word_count 
            FROM 
                Kytschi\Makabe\Models\Words 
            WHERE word=:word: 
            GROUP BY word
            LIMIT 1');

        $count = $query->execute(
            [
                'word' => $word
            ]
        );

        if ($count->count()) {
            return $count->getFirst()->word_count;
        }

        return 0;
    }

    private function buildQueries(
        $table,
        $content
    ) {
        if (empty($content)) {
            return;
        }

        if ($db_words = Words::find(['conditions' => 'deleted_at IS NULL'])) {
            foreach ($db_words as $db_word) {
                if (in_array($db_word->word, $this->ignore_words)) {
                    continue;
                }

                $this->ignore_words[] = $db_word->word;
            }
        }
        
        if ($words = preg_split('/\s|,/', $content, 0, PREG_SPLIT_NO_EMPTY)) {
            foreach ($words as $key => $word) {
                if (empty($word)) {
                    continue;
                }
                
                $word = rtrim($word, ',.;:');
                if (empty($word)) {
                    continue;
                }

                if (in_array(strtolower($word), $this->ignore_words)) {
                    continue;
                }

                if (is_numeric($word) || strlen($word) == 1) {
                    continue;
                }

                $this->buildQuery(
                    $table,
                    substr_count($content, $word),
                    $key,
                    $word
                );
            }
        }
    }

    private function buildQuery($table, $count, $key, $word)
    {
        $this->query .=
            'INSERT INTO ' . $table . '
                (id, resource_id, resource, word, word_count, campaign_id, created_at, created_by, updated_at, updated_by)
                    SELECT
                        :id_' . $key . ',
                        :resource_id,
                        :resource,
                        :word_' . $key . ',
                        :word_count_' . $key . ',
                        :campaign_id,
                        :created_at,
                        :created_by,
                        :updated_at,
                        :updated_by
                    FROM DUAL
                    WHERE NOT EXISTS
                    (
                        SELECT
                            id,
                            resource_id,
                            resource,
                            word,
                            word_count,
                            campaign_id,
                            created_at,
                            created_by,
                            updated_at,
                            updated_by
                        FROM ' . $table . '
                        WHERE
                            resource_id=:resource_id AND
                            resource=:resource AND
                            campaign_id=:campaign_id AND
                            word=:word_' . $key . ' AND
                            word_count=:word_count_' . $key . '
                    );
                UPDATE ' . $table . ' SET deleted_at=NULL, deleted_by=NULL
                WHERE
                    resource_id=:resource_id AND
                    resource=:resource AND 
                    campaign_id=:campaign_id AND 
                    word=:word_' . $key . ' AND
                    word_count=:word_count_' . $key . ';';

        $this->params = array_merge(
            $this->params,
            [
                ':id_' . $key => (new Random())->uuid(),
                ':word_' . $key => $word,
                ':word_count_' . $key => $count
            ]
        );
    }

    public function deleteAction($id = null)
    {
        $this->clearFormData();
        $this->secure($this->access);

        if (!$id) {
            $id = $this->dispatcher->getParam('id');
        }

        $model = (new Words())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $id
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->softDelete();

            $params = [
                ':word' => $model->word,
                ':resource_id' => $model->resource_id,
                ':user_id' => self::getUserId()
            ];

            $query = 'UPDATE ' . $model->getSource() . ' 
                SET deleted_at = NOW(), deleted_by=:user_id
                WHERE word=:word AND ';
            
            if ($model->campaign_id) {
                $query .= '(resource_id = :resource_id OR campaign_id=:campaign_id)';
                $params[':campaign_id'] = $model->campaign_id;
            } else {
                $query .= 'resource_id = :resource_id';
            }

            $this
                ->db
                ->query(
                    $query,
                    $params
                );

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            if ($this->dispatcher->getParam('id')) {
                $url = $this->global_url . '/edit/' . $model->id;
                if (!empty($_GET['from'])) {
                    $url = urldecode($_GET['from']);
                }

                $this->saveFormDeleted('Word has been deleted');
                $this->redirect(UrlHelper::backend(rtrim($url, '/')));
            }
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function deleteAllAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        if (!empty($_GET['from'])) {
            $url = urldecode($_GET['from']);
        }

        if (empty($_POST['delete_words'])) {
            $this->saveFormError('Nothing to delete');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        }

        try {
            $model = null;

            $query = '';
            $params = [
                ':resource' => $this->resource,
                ':summary' => 'Deleted by ' . $this->getUserFullName(),
                ':user_id' => self::getUserId()
            ];
            $table_log = (new LogsModel())->getSource();

            $key = 0;

            foreach ($_POST['delete_words'] as $id => $word) {
                if (empty($model)) {
                    $model = (new Words())->findFirst([
                        'conditions' => 'id = :id:',
                        'bind' => [
                            'id' => $id
                        ]
                    ]);
                    if (empty($model)) {
                        return $this->notFound();
                    }

                    $params[':resource_id'] = $model->resource_id;
                    $table = $model->getSource();
                }

                $params[':word_' . $key] = $word;
    
                $query .= 'UPDATE ' . $table . ' 
                    SET deleted_at = NOW(), deleted_by=:user_id
                    WHERE word=:word_' . $key . ' AND ';
                
                if ($model->campaign_id) {
                    $query .= '(resource_id = :resource_id OR campaign_id=:campaign_id);';
                    $params[':campaign_id'] = $model->campaign_id;
                } else {
                    $query .= 'resource_id = :resource_id;';
                }

                $query .= 'INSERT INTO ' . $table_log . ' 
                    SET
                        id=:id_' . $key . ',
                        created_at=NOW(),
                        created_by=:user_id,
                        updated_at=NOW(),
                        updated_by=:user_id,
                        resource=:resource,
                        resource_id=:word_id_' . $key . ',
                        summary=:summary,
                        type="danger";';

                $params[':id_' . $key] = (new Random())->uuid();
                $params[':word_id_' . $key] = $id;
                
                $key++;
            }

            $this
                ->db
                ->query(
                    $query,
                    $params
                );

            $this->saveFormDeleted('Words have been deleted');
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

        $model = (new Words())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle($model->word);

        return $this->view->partial(
            'makabe/page_scanner/exclude_words/edit',
            [
                'data' => $model
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Exclude words');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'word',
                'created_by'
            ],
            'word'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Words::class)
            ->where('resource="exclude"')
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'word' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('word LIKE :word:');
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
            'makabe/page_scanner/exclude_words/index',
            [
                'data' => $paginator->paginate()
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Words())->findFirst([
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

            $params = [
                ':word' => $model->word,
                ':resource_id' => $model->resource_id
            ];

            $query = 'UPDATE ' . $model->getSource() . ' 
                SET deleted_at = null, deleted_by=null
                WHERE word=:word AND ';
            
            if ($model->campaign_id) {
                $query .= '(resource_id = :resource_id OR campaign_id=:campaign_id)';
                $params[':campaign_id'] = $model->campaign_id;
            } else {
                $query .= 'resource_id = :resource_id';
            }

            $this
                ->db
                ->query(
                    $query,
                    $params
                );


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

            $this->saveFormUpdated('Word has been recovered');
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
        $this->global_url = ($this->di->getConfig())->urls->mms . '/exclude-words';

        $this->secure($this->access);

        try {
            $this->validate();

            $model = $this->setData(new Words());

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to add the word',
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

            $this->saveFormSaved('Word has been saved');
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

    public function setData($model)
    {
        $model->word = strtolower($_POST['word']);
        $model->resource = $_POST['resource'];

        return $model;
    }

    public function updateAction()
    {
        $this->global_url = ($this->di->getConfig())->urls->mms . '/exclude-words';

        $this->secure($this->access);

        $model = (new Words())->findFirst([
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
                    'Failed to update the word',
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

            $this->saveFormUpdated('Word has been updated');

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
            'word',
            new PresenceOf(
                [
                    'message' => 'The word is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
