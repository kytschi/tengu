<?php

/**
 * Statement item invoices model.
 *
 * @package     Kytschi\Wako\Models\StatementItemInvoices
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Wako\Models;

use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Wako\Models\Invoices;
use Kytschi\Wako\Models\StatementItem;

class StatementItemInvoices extends Model
{
    public $statement_item_id;
    public $invoice_id;

    public function initialize()
    {
        $this->setSource('wako_statement_item_invoices');

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
            'statement_item_id',
            StatementItems::class,
            'id',
            [
                'alias'    => 'statement_item',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'invoice_id',
            Invoices::class,
            'id',
            [
                'alias'    => 'invoice',
                'reusable' => true
            ]
        );
    }
}
