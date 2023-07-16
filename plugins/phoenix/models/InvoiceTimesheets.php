<?php

/**
 * Invoice timesheets model.
 *
 * @package     Kytschi\Wako\Models\InvoiceTimesheets
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

class InvoiceTimesheets extends Model
{
    public $invoice_id;
    public $timesheet_id;

    public function initialize()
    {
        $this->setSource('wako_invoice_timesheets');
    }
}
