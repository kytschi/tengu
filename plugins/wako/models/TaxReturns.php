<?php

/**
 * Tax returns model.
 *
 * @package     Kytschi\Wako\Models\TaxReturns
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
use Kytschi\Wako\Models\TaxReturnEmployments;
use Kytschi\Wako\Models\TaxReturnSelfAssessment;
use Kytschi\Wako\Models\TaxYears;

class TaxReturns extends Model
{
    public $tax_year_id;
    public $user_id;
    public $type = 'self-assessment';
    public $search_tags;
    public $return_completed_on;
    public $status = 'pending';

    public function initialize()
    {
        $this->setSource('wako_tax_returns');

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
            'user_id',
            Users::class,
            'id',
            [
                'alias'    => 'employee',
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
            TaxReturnEmployments::class,
            'tax_return_id',
            [
                'alias'    => 'employment_return',
                'reusable' => true
            ]
        );

        $this->hasMany(
            'tax_year_id',
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
            'tax_year_id',
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

    public function getReturnData()
    {
        switch ($this->type) {
            case 'self-assessment':
                return (new TaxReturnSelfAssessment())->findFirst([
                    'conditions' => 'tax_return_id = :tax_return_id:',
                    'bind' => [
                        'tax_return_id' => $this->id
                    ]
                ]);
                break;
            default:
                return null;
                break;
        }
    }

    public function getStatementItems()
    {
        return (new StatementItems())->find([
            'conditions' => 'tax_year_id = :tax_year_id: AND
                (shareholder_id = :user_id: OR employee_id = :user_id: OR expenses_employee_id = :user_id:)',
            'bind' => [
                'tax_year_id' => $this->tax_year_id,
                'user_id' => $this->user_id
            ]
        ]);
    }
}
