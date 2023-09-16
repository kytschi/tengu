<?php

/**
 * Dividends model.
 *
 * @package     Kytschi\Wako\Models\Dividends
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Wako\Models;

use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Projects;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Wako\Models\Settings;
use Kytschi\Wako\Models\StatementItems;
use Kytschi\Wako\Models\TaxYears;
use Kytschi\Wako\Traits\TaxYear;

class Dividends extends Model
{
    use TaxYear;

    public $shareholder_id;
    public $statement_item_id;
    public $tax_year_id;
    public $number;
    public $amount;
    public $issued_on;
    public $paid_on;
    public $status = 'pending';

    public function initialize()
    {
        $this->setSource('wako_dividends');

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
            'shareholder_id',
            Users::class,
            'id',
            [
                'alias'    => 'shareholder',
                'reusable' => true
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

        $this->hasOne(
            'statement_item_id',
            StatementItems::class,
            'id',
            [
                'alias'    => 'statement_item',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'id',
            Files::class,
            'resource_id',
            [
                'alias'    => 'file',
                'reusable' => true,
                'params' => [
                    'conditions' => 'deleted_at IS NULL'
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
    }

    public function getLatestNumber()
    {
        $model = new self();
        $number = (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query('SELECT count(id) AS number FROM ' . $model->getSource())
        ))->toArray();
        
        if ($number) {
            return $number[0]['number'] + 1;
        } else {
            return 1;
        }
    }

    public function inCurrentTaxYear()
    {
        $tax_year = $this->getCurrentTaxYear();

        if ($this->issued_on >= $tax_year->tax_year_start && $this->issued_on <= $tax_year->tax_year_end) {
            return true;
        }

        return false;
    }
}
