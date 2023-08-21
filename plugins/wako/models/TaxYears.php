<?php

/**
 * Tax years model.
 *
 * @package     Kytschi\Wako\Models\TaxYears
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
use Kytschi\Wako\Models\Invoices;
use Kytschi\Wako\Models\Receipts;
use Kytschi\Wako\Models\StatementItems;

class TaxYears extends Model
{
    public $code;
    public $tax_year_start;
    public $tax_year_end;
    public $dividend_allowance;
    public $dividend_allowance_tax;
    public $search_tags;
    public $return_due_by;
    public $return_completed_on;
    public $status = 'current';

    public function initialize()
    {
        $this->setSource('wako_tax_years');

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
            'tax_year_id',
            [
                'alias'    => 'statement_items',
                'reusable' => true,
                'params'   => [
                    'order' => 'processed_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Receipts::class,
            'tax_year_id',
            [
                'alias'    => 'receipts',
                'reusable' => true,
                'params'   => [
                    'order' => 'issued_on DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Invoices::class,
            'tax_year_id',
            [
                'alias'    => 'invoices',
                'reusable' => true,
                'params'   => [
                    'order' => 'issued_on DESC'
                ]
            ]
        );
    }
}
