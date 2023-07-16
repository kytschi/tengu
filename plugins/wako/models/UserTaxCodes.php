<?php

/**
 * User tax codes model.
 *
 * @package     Kytschi\Wako\Models\UserTaxCodes
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
use Kytschi\Wako\Models\TaxYears;

class UserTaxCodes extends Model
{
    public $user_id;
    public $tax_year_id;
    public $code;
    public $percentage = 20;
    public $allowance = 12750;

    public function initialize()
    {
        $this->setSource('wako_user_tax_codes');

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
            'tax_year_id',
            TaxYears::class,
            'id',
            [
                'alias'    => 'tax_year',
                'reusable' => true
            ]
        );
    }
}
