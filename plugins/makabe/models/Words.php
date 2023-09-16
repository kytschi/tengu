<?php

/**
 * Words model.
 *
 * @package     Kytschi\Makabe\Models\Words
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Makabe\Models;

use Kytschi\Makabe\Models\Keywords;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;

class Words extends Model
{
    public $campaign_id;
    public $resource;
    public $resource_id;
    public $word;
    public $word_count = 0;

    public function initialize()
    {
        $this->setSource('makabe_words');

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

        $this->hasMany(
            'id',
            Notes::class,
            'resource_id',
            [
                'alias'    => 'notes',
                'reusable' => true,
                'params'   => [
                    'order' => 'created_at DESC'
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
                    'conditions' => 'deleted_at IS NULL',
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
                'reusable' => true,
                'params'   => [
                    'order' => 'created_at DeSC'
                ]
            ]
        );

        $this->hasOne(
            'word',
            Keywords::class,
            'keyword',
            [
                'alias'    => 'keyword',
                'reusable' => true
            ]
        );
    }

    public function getPopularity()
    {
        if ($this->word_count <= 10) {
            return 'Not very';
        } elseif ($this->word_count <= 20) {
            return 'Pretty popular';
        } elseif ($this->word_count <= 30) {
            return 'Somewhat popular';
        } elseif ($this->word_count <= 40) {
            return 'Popular';
        } elseif ($this->word_count <= 50) {
            return 'Very popular';
        } else {
            return 'Extremely popular';
        }
    }
}
