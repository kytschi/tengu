<?php

/**
 * Statement item receipts model.
 *
 * @package     Kytschi\Wako\Models\StatementItemReceipts
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Wako\Models;

use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Wako\Models\Receipts;
use Kytschi\Wako\Models\StatementItem;

class StatementItemReceipts extends Model
{
    public $statement_item_id;
    public $receipt_id;
    public $sync = false;

    public function initialize()
    {
        $this->setSource('wako_statement_item_receipts');

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
            'receipt_id',
            Receipts::class,
            'id',
            [
                'alias'    => 'receipt',
                'reusable' => true
            ]
        );
    }
}
