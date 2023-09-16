<?php

/**
 * Barclays business parser.
 *
 * @package     Kytschi\Wako\Parsers\BarclaysBusinessParser
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
use Kytschi\Wako\Models\StatementItems;
use Phalcon\Encryption\Security\Random;

class BarclaysBusinessParser extends ControllerBase
{
    public function parse($lines, $model, $type = '')
    {
        if (!$type) {
            foreach ($lines as $line) {
                switch (strtolower($line)) {
                    case 'barclays.co.uk':
                        $type = 'barclays';
                        break;
                    case strtolower('Your Business Current Account'):
                        $business = true;
                        break;
                    default:
                        break;
                }

                if ($type) {
                    break;
                }
            }

            if (!$type) {
                return null;
            }

            if ($type == 'barclays' && $business) {
                $type = 'barclays-business';
            }
        }

        switch ($type) {
            case 'barclays-business':
                $model->type = $type;
                $model->currency = 'GBP';
                return $this->process($lines, $model);
                break;
            default:
                return null;
                break;
        }
    }

    private function process($lines, $model)
    {
        $record = false;
        $moneys = false;
        $period = false;
        $new = false;
        $data = [];
        $year = '';
        
        foreach ($lines as $line) {
            if (substr(strtolower($line), 0, strlen('Start balance')) == strtolower('Start balance')) {
                $moneys = true;
                $new = true;
                continue;
            }

            if (strtolower($line) == strtolower('At a glance')) {
                $period = true;
                continue;
            }

            if (strtolower($line) == strtolower('Date Description Money out £ Money in £ Balance £')) {
                $record = true;
                $new = true;
                continue;
            }

            if (strtolower($line) == strtolower('Balance brought forward from previous page')) {
                $new = true;
                continue;
            }

            if ($period) {
                $splits = array_reverse(explode('-', $line));
                $dates = [];
                foreach ($splits as $key => $date) {
                    $date = trim($date);
                    if (strlen($date) < 8 && $year) {
                        $dates[$key] = date('Y-m-d', strtotime($date . ' ' . $year));
                    } else {
                        $dates[$key] = date('Y-m-d', strtotime($date));
                        $year = date('Y', strtotime($date));
                    }
                }

                if (!empty($dates[1])) {
                    $model->period_from = $dates[1];
                }
                if (!empty($dates[0])) {
                    $model->period_to = $dates[0];
                }
                $period = false;
            }

            if ($moneys) {
                if (substr(strtolower($line), 0, strlen('Money out')) == strtolower('Money out')) {
                    $model->out = NumberHelper::fromCurrency($line);
                    continue;
                } elseif (substr(strtolower($line), 0, strlen('Money in')) == strtolower('Money in')) {
                    $model->in = NumberHelper::fromCurrency($line);
                    continue;
                }
            }
            
            if ($record) {
                if (strpos(strtolower($line), strtolower('Start balance')) !== false) {
                    continue;
                }

                //echo $line . '<br/>';

                if ($new) {
                    $arr = [];
                    $new = false;
                }

                if (empty($arr['date'])) {
                    if (($found = DateHelper::find($line, $year))) {
                        if (!empty($found['month']) && !empty($found['day']) && !empty($found['day'])) {
                            $arr['date'] = $found['year'] . '-' . $found['month'] . '-' . $found['day'];
                        }
                    }
                }

                $arr[] = $line;

                $checks = explode(' ', $line);
                if (isset($checks[1])) {
                    if (
                        is_numeric(NumberHelper::fromCurrency($checks[0])) &&
                        is_numeric(NumberHelper::fromCurrency($checks[1]))
                    ) {
                        $new = true;
                        $data[] = $arr;
                    }
                }
            }
        }
        
        if ($data) {
            $user_id = self::getUserId();

            $this
                ->modelsManager
                ->executeQuery(
                    'UPDATE ' .
                    StatementItems::class .
                    ' SET deleted_at=NOW(), deleted_by=:deleted_by: WHERE statement_id = :statement_id:',
                    [
                        'deleted_by' => $user_id,
                        'statement_id' => $model->id
                    ]
                );
            
            $table = (new StatementItems())->getSource();
            $params = [
                ':statement_id' => $model->id,
                ':created_at' => date('Y-m-d H:i:s'),
                ':created_by' => $user_id,
                ':updated_at' => date('Y-m-d H:i:s'),
                ':updated_by' => $user_id
            ];

            $query = '';
            $ins = [
                'direct credit',
                'refund'
            ];
            $key = 0;
            foreach ($data as $arr) {
                $date = null;
                if (isset($arr['date'])) {
                    if (!($date = $arr['date'])) {
                        $date = null;
                    }
                    unset($arr['date']);
                }
                $direction = 'out';
                foreach ($ins as $in_str) {
                    if (strpos(strtolower($arr[0]), $in_str) !== false) {
                        $direction = 'in';
                        break;
                    }
                }

                $in = 0;
                $out = 0;
                $amounts = explode(' ', array_pop($arr));
                if ($direction == 'in') {
                    $in = NumberHelper::fromCurrency($amounts[0]);
                } else {
                    $out = NumberHelper::fromCurrency($amounts[0]);
                }
                $balance = NumberHelper::fromCurrency($amounts[1]);

                $description = implode("\n", $arr);

                $params = array_merge(
                    $params,
                    [
                        ':id_' . $key => (new Random())->uuid(),
                        ':description_' . $key => self::encrypt($description),
                        ':in_' . $key => $in,
                        ':out_' . $key => $out,
                        ':balance_' . $key => $balance,
                        ':processed_at_' . $key => $date
                    ]
                );
    
                $query .= '
                    INSERT INTO ' . $table .
                    '(
                        id,
                        statement_id,
                        description, 
                        `in`, 
                        `out`, 
                        `balance`, 
                        processed_at, 
                        created_at, 
                        created_by, 
                        updated_at, 
                        updated_by
                    )
                    SELECT
                        :id_' . $key . ',
                        :statement_id,
                        :description_' . $key . ',
                        :in_' . $key . ',
                        :out_' . $key . ',
                        :balance_' . $key . ',
                        :processed_at_' . $key . ',
                        :created_at,
                        :created_by,
                        :updated_at,
                        :updated_by
                    FROM DUAL WHERE NOT EXISTS
                    (
                        SELECT 
                            id, 
                            statement_id, 
                            description, 
                            `in`, 
                            `out`, 
                            `balance`, 
                            processed_at, 
                            created_at, 
                            created_by, 
                            updated_at, 
                            updated_by
                        FROM ' . $table . '
                        WHERE statement_id=:statement_id AND description=:description_' . $key . ' 
                    );
                UPDATE ' . $table . '
                SET 
                    deleted_at=NULL,
                    deleted_by=NULL, 
                    balance=:balance_' . $key . ', 
                    `in`=:in_' . $key . ', 
                    `out`=:out_' . $key . ',
                    processed_at=:processed_at_' . $key . ' 
                WHERE statement_id=:statement_id AND description=:description_' . $key . ';';
                $key++;
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
