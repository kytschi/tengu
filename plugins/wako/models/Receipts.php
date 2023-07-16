<?php

/**
 * Receipts model.
 *
 * @package     Kytschi\Wako\Models\Receipts
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Wako\Models;

use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Wako\Models\StatementItemReceipts;
use Kytschi\Wako\Models\StatementItems;
use Kytschi\Wako\Models\TaxYears;

class Receipts extends Model
{
    public $tax_year_id;
    public $name;
    public $amount;
    public $original_amount;
    public $currency;
    public $issued_on;
    public $taxable = 1;
    public $vat;
    public $sub_total;
    public $status = 'active';

    public function initialize()
    {
        $this->setSource('wako_receipts');

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
            'tax_year_id',
            TaxYears::class,
            'id',
            [
                'alias'    => 'tax_year',
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

        $this->hasManyToMany(
            'id',
            StatementItemReceipts::class,
            'receipt_id',
            'statement_item_id',
            StatementItems::class,
            'id',
            [
                'alias'    => 'statement_items',
                'reusable' => true,
                'params'   => [
                    'conditions' => StatementItemReceipts::class . '.deleted_at IS NULL',
                    'order' => 'processed_at DESC'
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
}
