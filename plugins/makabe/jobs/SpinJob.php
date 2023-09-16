<?php

/**
 * Spin job controller.
 *
 * @package     Kytschi\Makabe\Jobs\SpinJob
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Makabe\Jobs;

use Kytschi\Makabe\Exceptions\SpinException;
use Kytschi\Makabe\Models\SpinContent;
use Kytschi\Makabe\Models\SpunContent;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notifications;
use Kytschi\Tengu\Traits\Core\User;
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder;

class SpinJob extends Controller
{
    use Logs;
    use Notifications;
    use User;

    private $max_spins = 100000;
    private $job;
    private $keywords = [];

    public $resource = 'spin-job';

    private function build($source, $type)
    {
        $this->keywords[$type] = [];

        if (empty($source)) {
            return [[false]];
        }

        preg_match_all("/{+(.*?)}/", $source, $this->keywords[$type]);
        if (empty($this->keywords[$type])) {
            return [[false]];
        }

        $content_words = [];
        $spins = 1;

        foreach ($this->keywords[$type][1] as $key => $words) {
            $splits = explode('|', $words);
            $content_words[$key] = $splits;
            $spins *= count($splits);
        }

        if ($spins > $this->max_spins) {
            $this->notify(
                $this->job->created_by,
                $this->job->resource,
                $this->job->resource_id,
                'danger',
                'You have exceeded the maximum spin count of ' . $this->max_spins,
                'You are trying to spin ' . $spins .
                ' amount of data which is too great. Please try reducing the number of keywords.'
            );
            throw new SpinException('You have exceeded the maximum spin count of ' . $this->max_spins);
        }

        $content = [[]];
        for ($iLoop = 0; $iLoop < count($content_words); $iLoop++) {
            $words = [];
            foreach ($content as $v1) {
                foreach ($content_words[$iLoop] as $v2) {
                    $words[] = array_merge($v1, [$v2]);
                }
            }

            $content = $words;
        }

        return $content;
    }

    private function createSlug($string)
    {
        return str_replace(
            ' ',
            '-',
            str_replace(['?'], '', strtolower($string))
        );
    }

    public function run($job)
    {
        $this->job = $job;
        $this->max_spins = intval(($this->di->getConfig())->makabe->max_spins);

        $model = (new SpinContent())->findFirst([
            'conditions' => 'deleted_at IS NULL AND id=:id:',
            'bind' => [
                'id' => $this->job->resource_id
            ]
        ]);
        if (empty($model)) {
            return;
        }

        $this
            ->modelsManager
            ->executeQuery(
                'UPDATE ' .
                SpunContent::class .
                ' SET deleted_at=NOW(), deleted_by=:deleted_by: 
                WHERE spin_content_id = :spin_content_id:',
                [
                    'deleted_by' => self::getUserId(),
                    'spin_content_id' => $model->id
                ]
            );

        $this->spin($model);

        $this->notify(
            $job->created_by,
            $job->resource,
            $job->resource_id,
            'info',
            'Content has been successfully spun'
        );
    }

    private function save($model, $sort, $content, $title, $url, $meta_keyword, $meta_description)
    {
        $spun = new SpunContent([
            'resource_id' => $model->resource_id,
            'resource' => $model->resource,
            'spin_content_id' => $model->id,
            'content' => $content,
            'title' => $title,
            'url' => $url,
            'canonical_url' => $url,
            'meta_keywords' => $meta_keyword,
            'meta_description' => $meta_description,
            'label' => $model->label . ' (' . ($sort + 1) . ')',
            'sort' => $sort
        ]);

        if ($spun->save() === false) {
            $this->addLog(
                $this->resource,
                $this->job->id,
                'danger',
                'Failed to add the spun content',
                implode(', ', $spun->getMessages())
            );

            $this->job->status = 'failed';
            if ($this->job->update() === false) {
                $this->addLog(
                    $this->resource,
                    $this->job->id,
                    'danger',
                    'Failed to update the job'
                );

                throw new SaveException(
                    'Failed to update the job',
                    $this->job->getMessages()
                );
            }

            throw new SaveException(
                'Failed to add the spun content',
                $spun->getMessages()
            );
        }
    }

    private function spin($model)
    {
        $spun = [];
        $spun['content'] = $this->build($model->content, 'content');
        $spun['name'] = $this->build($model->name, 'name');
        $spun['url'] = $this->build($model->url, 'url');
        //$spun['canonical_url'] = $this->build($model->canonical_url, 'canonical_url);
        $spun['meta_keywords'] = $this->build($model->meta_keywords, 'meta_keywords');
        $spun['meta_description'] = $this->build($model->meta_description, 'meta_description');

        $spin = [
            'content' => '',
            'name' => '',
            'url' => '',
            'meta_keywords' => '',
            'meta_description' => ''
        ];

        $sort = 1;

        foreach ($spun['content'] as $c_words) {
            $spin['content'] = $model->content;
            foreach ($c_words as $key => $word) {
                $spin['content'] = str_replace($this->keywords['content'][0][$key], $word, $spin['content']);
            }

            foreach ($spun['name'] as $t_words) {
                $spin['name'] = $model->name;
                foreach ($t_words as $key => $word) {
                    if ($word === false) {
                        break;
                    }
                    $spin['name'] = str_replace($this->keywords['name'][0][$key], $word, $spin['name']);
                }

                foreach ($spun['url'] as $u_words) {
                    $spin['url'] = $model->url;
                    foreach ($u_words as $key => $word) {
                        if ($word === false) {
                            break;
                        }
                        $spin['url'] = str_replace($this->keywords['url'][0][$key], $word, $spin['url']);
                    }

                    if ($spin['url']) {
                        $spin['url'] = $this->createSlug($spin['url']);
                    }

                    foreach ($spun['meta_keywords'] as $mk_words) {
                        $spin['meta_keywords'] = $model->meta_keywords;
                        foreach ($mk_words as $key => $word) {
                            if ($word === false) {
                                break;
                            }
                            $spin['meta_keywords'] = str_replace(
                                $this->keywords['meta_keywords'][0][$key],
                                $word,
                                $spin['meta_keywords']
                            );
                        }

                        foreach ($spun['meta_description'] as $md_words) {
                            $spin['meta_description'] = $model->meta_description;
                            foreach ($md_words as $key => $word) {
                                if ($word === false) {
                                    break;
                                }
                                $spin['meta_description'] = str_replace(
                                    $this->keywords['meta_description'][0][$key],
                                    $word,
                                    $spin['meta_description']
                                );
                            }

                            $this->save(
                                $model,
                                $sort,
                                $spin['content'],
                                $spin['name'],
                                $spin['url'],
                                $spin['meta_keywords'],
                                $spin['meta_description']
                            );

                            $sort++;
                        }
                    }
                }
            }
        }
    }
}
