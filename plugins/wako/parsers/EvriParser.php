<?php

/**
 * Evri invoice parser.
 *
 * @package     Kytschi\Wako\Parsers\EvriParser
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

declare(strict_types=1);

namespace Kytschi\Wako\Parsers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\NumberHelper;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Wako\Models\Receipts;
use Phalcon\Encryption\Security\Random;

class EvriParser extends ControllerBase
{
    public function parse($lines, $model, $type = '')
    {
        if (!$type) {
            foreach ($lines as $line) {
                if (strpos(strtolower($line), strtolower('Hermes Parcelnet Ltd')) !== false) {
                    $type = 'evri';
                    break;
                }

                if (strpos(strtolower($line), strtolower('Evri')) !== false) {
                    $type = 'evri';
                    break;
                }
            }
                                    
            if (!$type) {
                return null;
            }
        }

        switch ($type) {
            case 'evri':
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
            if (empty($sub_total)) {
                if (substr(strtolower($line), 0, strlen('Subtotal (ext-VAT)')) == strtolower('Subtotal (ext-VAT)')) {
                    $sub_total = NumberHelper::fromCurrency(trim(str_replace('Subtotal (ext-VAT)', '', $line)));
                    continue;
                }
            }

            if (empty($amount)) {
                if (substr(strtolower($line), 0, strlen('Total (inc VAT)')) == strtolower('Total (inc VAT)')) {
                    $amount = NumberHelper::fromCurrency(trim(str_replace('Total (inc VAT)', '', $line)));
                    continue;
                }
            }

            if (empty($vat)) {
                if (substr(strtolower($line), 0, strlen('VAT (Code S at 20%)')) == strtolower('VAT (Code S at 20%)')) {
                    $vat = NumberHelper::fromCurrency(trim(str_replace('VAT (Code S at 20%)', '', $line)));
                    continue;
                }
            }

            if (substr(strtolower($line), 0, strlen('Invoice number: ')) == strtolower('Invoice number: ')) {
                $refs[] = trim(str_replace('Invoice number: ', '', $line));
                continue;
            }

            if (empty($issued_on)) {
                if (substr(strtolower($line), 0, strlen('Tax point:')) == strtolower('Tax point:')) {
                    $issued_on = DateHelper::sql(
                        trim(str_replace('Tax point:', '', $line)),
                        false
                    );
                    continue;
                }
            }
        }
        
        if (!$model->statement_items->count()) {
            $model->amount = $amount;
            $model->currency = 'GBP';
        }

        $model->vat = $vat;
        $model->sub_total = $sub_total;
        $model->original_amount = $amount;
        $model->original_currency = 'GBP';
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
