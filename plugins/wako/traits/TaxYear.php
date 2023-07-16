<?php

/**
 * Tax year traits.
 *
 * @package     Kytschi\Wako\Traits\TaxYear
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright 2022 Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Wako\Traits;

use Kytschi\Tengu\Traits\Core\Filters;
use Kytschi\Wako\Models\TaxYears;

trait TaxYear
{
    use Filters;

    public function getCurrentTaxYear()
    {
        if ($filter_year = $this->getFilters('year')) {
            if (
                $model = (new TaxYears())->findFirst([
                    'conditions' => 'id = :id: AND deleted_at IS NULL',
                    'bind' => [
                        'id' => $filter_year
                    ]
                ])
            ) {
                return $model;
            }
        }

        if (
            $model = (new TaxYears())->findFirst([
                'conditions' => 'status = "current" AND deleted_at IS NULL'
            ])
        ) {
            return $model;
        }

        return new TaxYears([
            'tax_year_start' => date('Y-m-d'),
            'tax_year_end' => date('Y-m-d', strtotime('+1 year')),
            'code' => 'NOT SET'
        ]);
    }

    public function getTaxCodeInfo($code)
    {
        $obj = new \stdClass();
        $obj->percentage = 0;
        $obj->allowance = 0;

        switch (strtoupper($code)) {
            case '1257L':
                $obj->percentage = 20;
                $obj->allowance = 12750;
                break;
        }

        return $obj;
    }

    public function getTaxYearId($date)
    {
        if (
            $model = (new TaxYears())->findFirst([
                'conditions' => '(:date: BETWEEN tax_year_start AND tax_year_end) AND deleted_at IS NULL',
                'bind' => [
                    'date' => $date
                ]
            ])
        ) {
            return $model->id;
        }

        return null;
    }

    public function getTaxYears()
    {
        return  (new TaxYears())->find([
                'conditions' => 'deleted_at IS NULL',
                'order' => 'tax_year_start DESC'
        ]);
    }
}
