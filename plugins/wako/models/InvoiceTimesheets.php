<?php

/**
 * Invoice timesheets model.
 *
 * @package     Kytschi\Wako\Models\InvoiceTimesheets
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
