<?php

/**
 * Statement items model.
 *
 * @package     Kytschi\Wako\Models\StatementItems
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

namespace Kytschi\Wako\Models;

use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Umi\Models\Projects;
use Kytschi\Wako\Models\Dividends;
use Kytschi\Wako\Models\Invoices;
use Kytschi\Wako\Models\Receipts;
use Kytschi\Wako\Models\StatementItemInvoices;
use Kytschi\Wako\Models\StatementItemReceipts;
use Kytschi\Wako\Models\Statements;
use Kytschi\Wako\Models\TaxYears;

class StatementItems extends Model
{
    public $statement_id;
    public $shareholder_id;
    public $tax_year_id;
    public $employee_id;
    public $expenses_employee_id;
    public $project_id = null;
    public $description;
    public $in;
    public $out;
    public $balance;
    public $taxable = false;
    public $processed_at;

    protected $encrypted = [
        'description'
    ];

    public function initialize()
    {
        $this->setSource('wako_statement_items');

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
            'employee_id',
            Users::class,
            'id',
            [
                'alias'    => 'employee',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'project_id',
            Projects::class,
            'id',
            [
                'alias'    => 'project',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'id',
            Dividends::class,
            'statement_item_id',
            [
                'alias'    => 'dividend',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'statement_id',
            Statements::class,
            'id',
            [
                'alias'    => 'statement',
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

        $this->hasManyToMany(
            'id',
            StatementItemReceipts::class,
            'statement_item_id',
            'receipt_id',
            Receipts::class,
            'id',
            [
                'alias'    => 'receipts',
                'reusable' => true,
                'params'   => [
                    'conditions' => StatementItemReceipts::class . '.deleted_at IS NULL',
                    'order' => 'issued_on DESC'
                ]
            ]
        );

        $this->hasManyToMany(
            'id',
            StatementItemInvoices::class,
            'statement_item_id',
            'invoice_id',
            Invoices::class,
            'id',
            [
                'alias'    => 'invoices',
                'reusable' => true,
                'params'   => [
                    'conditions' => StatementItemInvoices::class . '.deleted_at IS NULL',
                    'order' => 'issued_on DESC'
                ]
            ]
        );
    }
}
