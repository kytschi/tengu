<?php

/**
 * Invoices model.
 *
 * @package     Kytschi\Wako\Models\Invoices
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Wako\Models;

use Kytschi\Mai\Models\Timesheets;
use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Core\Projects;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Wako\Models\InvoiceTimesheets;
use Kytschi\Wako\Models\Settings;
use Kytschi\Wako\Models\StatementItems;
use Kytschi\Wako\Models\StatementItemInvoices;
use Kytschi\Wako\Models\TaxYears;
use Kytschi\Wako\Traits\TaxYear;

class Invoices extends Model
{
    use TaxYear;

    public $project_id = null;
    public $tax_year_id;
    public $name;
    public $amount;
    public $vat;
    public $sub_total;
    public $timesheet_amount = 0;
    public $search_tags;
    public $direction = 'outgoing';
    public $issued_on;
    public $paid_on;
    public $taxable = 1;
    public $ref;
    public $status = 'outstanding';

    public function initialize()
    {
        $this->setSource('wako_invoices');

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

        $this->hasOne(
            'project_id',
            Projects::class,
            'id',
            [
                'alias'    => 'project',
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

        $this->hasManyToMany(
            'id',
            InvoiceTimesheets::class,
            'invoice_id',
            'timesheet_id',
            Timesheets::class,
            'id',
            [
                'reusable' => true,
                'alias'    => 'timesheets',
                'params'   => [
                    'conditions' => InvoiceTimesheets::class . '.deleted_at IS NULL'
                ]
            ]
        );

        $this->hasManyToMany(
            'id',
            StatementItemInvoices::class,
            'invoice_id',
            'statement_item_id',
            StatementItems::class,
            'id',
            [
                'alias'    => 'statement_items',
                'reusable' => true,
                'params'   => [
                    'conditions' => StatementItemInvoices::class . '.deleted_at IS NULL',
                    'order' => 'processed_at DESC'
                ]
            ]
        );
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
