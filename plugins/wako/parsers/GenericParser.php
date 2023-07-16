<?php

/**
 * Generic invoice parser.
 *
 * @package     Kytschi\Wako\Parsers\GenericParser
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
use Kytschi\Tengu\Traits\Core\Currency;
use Kytschi\Wako\Exceptions\ParseException;
use Kytschi\Wako\Models\Receipts;
use Phalcon\Encryption\Security\Random;

class GenericParser extends ControllerBase
{
    use Currency;
    
    public function parse($lines, $model, $type = '')
    {
        $model->type = 'generic';
        return $this->process($lines, $model);
    }

    private function process($lines, $model)
    {
        $amount = 0.00;
        $vat = 0.00;
        $sub_total = 0.00;
        $refs = [];
        $issued_on = null;
        $currency = null;

        $dates = [
            'Date:'
        ];

        $order_ids = [
            'Order #',
            'Order number',
            'Order No:'
        ];

        $subtotals = [
            'Subtotal',
            'Net Amount'
        ];

        $totals = [
            'Grand Total',
            'Total paid',
            'Total Amount',
            'Total (GBP)',
            'Order Total:'
        ];

        $taxes = [
            'VAT',
            'Tax'
        ];
             
        foreach ($lines as $line) {
            //$this->dump($line, false);
            $found = false;
            foreach ($order_ids as $string) {
                if (strpos(strtolower($line), strtolower($string)) !== false) {
                    $splits = explode(':', rtrim(trim(str_replace($string, '', $line)), '.'));
                    $refs[] = trim(array_pop($splits));
                    $found = true;
                    break;
                }
            }
            if ($found) {
                continue;
            }

            foreach ($subtotals as $string) {
                if (substr(strtolower($line), 0, strlen($string)) == strtolower($string)) {
                    $sub_total = NumberHelper::fromCurrency(trim(str_replace($string, '', $line)));
                    break;
                }
            }

            foreach ($totals as $string) {
                if (substr(strtolower($line), 0, strlen($string)) == strtolower($string)) {
                    $amount = NumberHelper::fromCurrency(trim(str_replace($string, '', $line)));
                    break;
                }
            }

            foreach ($taxes as $string) {
                if (substr($line, 0, strlen($string)) == $string) {
                    $vat = NumberHelper::fromCurrency(trim(str_replace($string, '', $line)));
                    break;
                }
            }

            foreach ($dates as $string) {
                if (substr(strtolower($line), 0, strlen($string)) == strtolower($string)) {
                    $issued_on = DateHelper::sql(
                        trim(str_replace(['Date:', ','], '', $line)),
                        false
                    );
                    break;
                }
            }

            if (empty($currency)) {
                foreach ($this->currencies as $code => $currency_name) {
                    if (strpos($line, $code) !== false) {
                        $currency = $code;
                        break;
                    }
                }
            }
        }
                
        if (empty($currency)) {
            $currency = TENGU_CURRENCY;
        }

        if ($amount > 10000000000) {
            throw new ParseException('Invalid total', 'Number is too large');
        }
        
        if (!$model->statement_items->count()) {
            $model->amount = $amount;
            $model->currency = $currency;
        }

        if ($vat > 10000000000) {
            throw new ParseException('Invalid ' . NumberHelper::taxLabel(), 'Number is too large');
        }

        $model->vat = $vat;
        $model->sub_total = $sub_total;
        if (empty($sub_total) && $amount) {
            $model->sub_total = $amount - $vat;
        }

        if ($model->sub_total < 0) {
            $model->sub_total = 0.00;
        }

        if ($model->sub_total == $model->amount && $model->vat) {
            $model->sub_total = $amount - $vat;
        }

        $model->original_amount = $amount;
        $model->original_currency = $currency;
        
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
        
        if (empty($model->amount)) {
            return null;
        }

        return $model;
    }
}
