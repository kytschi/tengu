<?php

/**
 * Gandi invoice parser.
 *
 * @package     Kytschi\Wako\Parsers\GandiParser
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Wako\Parsers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\NumberHelper;
use Kytschi\Tengu\Models\Core\Tags;
use Kytschi\Wako\Models\Receipts;
use Phalcon\Encryption\Security\Random;

class GandiParser extends ControllerBase
{
    public function parse($lines, $model, $type = '')
    {
        if (!$type) {
            foreach ($lines as $line) {
                if (strpos(strtolower($line), strtolower('Gandi International')) !== false) {
                    $type = 'gandi-international';
                    break;
                }
            }
            
            if (!$type) {
                return null;
            }
        }

        switch ($type) {
            case 'gandi-international':
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
        $record = 0;
             
        foreach ($lines as $line) {
            //$this->dump($line, false);
            if (substr(strtolower($line), 0, strlen('Total')) == strtolower('Total')) {
                $record++;
                continue;
            }

            if ($record <= 3 && $record) {
                if ($record == 1) {
                    $sub_total = NumberHelper::fromCurrency(trim($line));
                } elseif ($record == 2) {
                    $vat = NumberHelper::fromCurrency(trim($line));
                } elseif ($record == 3) {
                    $amount = NumberHelper::fromCurrency(trim($line));
                }
                $record++;
            }

            if (substr(strtolower($line), 0, strlen('Invoice N°')) == strtolower('Invoice N°')) {
                $refs[] = trim(str_replace('Invoice N°', '', $line));
                continue;
            }

            if (empty($sub_total)) {
                if (substr(strtolower($line), 0, strlen('Subtotal')) == strtolower('Subtotal')) {
                    $sub_total = NumberHelper::fromCurrency(trim($line));
                    continue;
                }
            }

            if (empty($issued_on)) {
                if (substr(strtolower($line), 0, strlen('Date:')) == strtolower('Date:')) {
                    $issued_on = DateHelper::sql(
                        trim(str_replace('Date:', '', $line)),
                        false
                    );
                    continue;
                }
            }
        }
        //die();
                                
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
