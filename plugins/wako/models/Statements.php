<?php

/**
 * Statements model.
 *
 * @package     Kytschi\Wako\Models\Statements
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Wako\Models;

use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Wako\Models\StatementItems;
use Kytschi\Wako\Models\TaxYears;

class Statements extends Model
{
    public $tax_year_id;
    public $name;
    public $in;
    public $out;
    public $period_from;
    public $period_to;
    public $type;

    public function initialize()
    {
        $this->setSource('wako_statements');

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
            'id',
            Files::class,
            'resource_id',
            [
                'alias'    => 'file',
                'reusable' => false,
                'params' => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );

        $this->hasOne(
            'tax_year_id',
            TaxYears::class,
            'id',
            [
                'alias'    => 'tax_year',
                'reusable' => true
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
                'reusable' => true,
                'params'   => [
                    'order' => 'created_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            StatementItems::class,
            'statement_id',
            [
                'alias'    => 'items',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'processed_at DESC'
                ]
            ]
        );
    }
}
