<?php

/**
 * Payment Gateways traits.
 *
 * @package     Kytschi\Phoenix\Traits\PaymentGateways
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Phoenix\Traits;

use Kytschi\Phoenix\Models\PaymentGateway;

trait PaymentGateways
{
    public function findPaymentGateways($data = [])
    {
        $query = '';
        $bind = [];

        if (!empty($data)) {
            if (!empty($data['where'])) {
                if (!empty($data['where']['query'])) {
                    $query = 'AND ' . $data['where']['query'];
                }

                if (!empty($data['where']['data'])) {
                    $bind = $data['where']['data'];
                }
            }
        }

        return PaymentGateway::find([
            'conditions' => 'status = "active" AND deleted_at IS NULL ' . $query,
            'bind' => $bind,
            'order' => 'default DESC'
        ]);
    }
}
