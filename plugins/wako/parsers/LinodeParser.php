<?php

/**
 * Linode invoice parser.
 *
 * @package     Kytschi\Wako\Parsers\LinodeParser
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Wako\Parsers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\NumberHelper;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Wako\Models\Receipts;
use Phalcon\Encryption\Security\Random;

class LinodeParser extends ControllerBase
{
    public function parse($lines, $model, $type = '')
    {
        if (!$type) {
            foreach ($lines as $line) {
                if (strpos(strtolower($line), strtolower('Linode')) !== false) {
                    $type = 'linode';
                    break;
                }
            }
            
            if (!$type) {
                return null;
            }
        }

        switch ($type) {
            case 'linode':
                $model->type = $type;
                return $this->process($lines, $model);
                break;
            default:
                return null;
                break;
        }
    }

    private function process($lines, $model)
    {
        $amount = 0.00;
        $vat = 0.00;
        $sub_total = 0.00;
        $refs = [];
        $issued_on = null;
             
        foreach ($lines as $line) {
            if (empty($amount)) {
                if (substr(strtolower($line), 0, strlen('Total (USD)')) == strtolower('Total (USD)')) {
                    $amount = NumberHelper::fromCurrency(trim(str_replace('Total (USD)', '', $line)));
                    continue;
                }
            }

            if (empty($vat)) {
                if (substr(strtolower($line), 0, strlen('Tax Subtotal (USD)')) == strtolower('Tax Subtotal (USD)')) {
                    $vat = NumberHelper::fromCurrency(trim(str_replace('Tax Subtotal (USD)', '', $line)));
                    continue;
                }
            }

            if (empty($sub_total)) {
                if (substr(strtolower($line), 0, strlen('Subtotal (USD)')) == strtolower('Subtotal (USD)')) {
                    $sub_total = NumberHelper::fromCurrency(trim(str_replace('Subtotal (USD)', '', $line)));
                    continue;
                }
            }

            if (substr(strtolower($line), 0, strlen('Invoice: #')) == strtolower('Invoice: #')) {
                $refs[] = trim(str_replace('Invoice: #', '', $line));
                continue;
            }

            if (empty($issued_on)) {
                if (substr(strtolower($line), 0, strlen('Invoice Date:')) == strtolower('Invoice Date:')) {
                    $issued_on = DateHelper::sql(
                        trim(str_replace('Date:', '', $line)),
                        false
                    );
                    continue;
                }
            }
        }
        
        if (!$model->statement_items->count()) {
            $model->amount = $amount;
            $model->currency = 'USD';
        }

        $model->vat = $vat;
        $model->sub_total = $sub_total;
        $model->original_amount = $amount;
        $model->original_currency = 'USD';
        if ($issued_on) {
            $model->issued_on = $issued_on;
        }

        if (count($refs)) {
            $user_id = self::getUserId();
            $query = '';
            $table = (new Tags())->getSource();

            $params = [
                ':resource' => 'receipt',
                ':resource_id' => $model->id,
                ':created_at' => date('Y-m-d H:i:s'),
                ':created_by' => $user_id,
                ':updated_at' => date('Y-m-d H:i:s'),
                ':updated_by' => $user_id
            ];

            foreach ($refs as $key => $ref) {
                if (empty($ref)) {
                    continue;
                }

                $params = array_merge(
                    $params,
                    [
                        ':id_' . $key => (new Random())->uuid(),
                        ':tag_' . $key => $ref
                    ]
                );

                $query .= 'INSERT INTO ' . $table .
                    '(
                        id, 
                        resource, 
                        resource_id, 
                        tag, 
                        created_at, 
                        created_by, 
                        updated_at, 
                        updated_by
                    )
                    SELECT
                        :id_' . $key . ',
                        :resource,
                        :resource_id,
                        :tag_' . $key . ',
                        :created_at,
                        :created_by,
                        :updated_at,
                        :updated_by
                    FROM DUAL WHERE NOT EXISTS
                    (
                        SELECT 
                            id, 
                            resource, 
                            resource_id, 
                            tag, 
                            created_at, 
                            created_by, 
                            updated_at, 
                            updated_by
                        FROM ' . $table . '
                        WHERE resource=:resource AND resource_id=:resource_id AND tag=:tag_' . $key . ' 
                    );
                UPDATE ' . $table . '
                SET 
                    deleted_at=NULL,
                    deleted_by=NULL 
                WHERE resource=:resource AND resource_id=:resource_id AND tag=:tag_' . $key . ';';
            }

            if (!empty($query)) {
                $this->db->query(
                    $query,
                    $params
                );
            }
        }
        
        return $model;
    }
}
