<?php

/**
 * Users model.
 *
 * @package     Kytschi\Tengu\Models\Core\Users
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi- All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Models\Core;

use Kytschi\Phoenix\Models\Orders;
use Kytschi\Tengu\Controllers\Core\GroupsController;
use Kytschi\Tengu\Models\Core\Files;
use Kytschi\Tengu\Models\Core\Groups;
use Kytschi\Tengu\Models\Core\Logs;
use Kytschi\Tengu\Models\Core\Notes;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Core\Notifications;
use Kytschi\Wako\Controllers\SettingsController;
use Kytschi\Wako\Models\StatementItems;
use Kytschi\Wako\Models\UserTaxCodes;
use Kytschi\Wako\Traits\TaxYear;

class Users extends Model
{
    use TaxYear;
    
    public $group_id;
    public $email;
    public $first_name;
    public $last_name;
    public $last_login;
    public $login_attempts = 0;
    public $temp_password = 0;
    public $job_title;
    public $company;
    public $phone;
    public $address_line_1;
    public $address_line_2;
    public $town;
    public $county;
    public $country;
    public $postcode;
    public $shareholder;
    public $utr;
    public $employee_ref;
    public $national_insurance;
    public $dob;
    public $type = 'customer';
    public $status = 'inactive';

    protected $encrypted = [
        'email',
        'first_name',
        'last_name',
        'phone',
        'address_line_1',
        'address_line_2',
        'town',
        'county',
        'country',
        'postcode',
        'utr',
        'employee_ref',
        'national_insurance',
        'dob'
    ];

    public function initialize()
    {
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

        $this->hasMany(
            'id',
            Notes::class,
            'resource_id',
            [
                'alias'    => 'notes',
                'reusable' => false,
                'params'   => [
                    'order' => 'created_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Tags::class,
            'resource_id',
            [
                'alias'    => 'tags',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL AND resource="user" AND type IS NULL',
                    'order' => 'tag'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Notifications::class,
            'to_user_id',
            [
                'alias'    => 'notifications',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Notifications::class,
            'to_user_id',
            [
                'alias'    => 'notifications_limit',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'limit' => 5
                ]
            ]
        );

        $this->hasOne(
            'id',
            Files::class,
            'resource_id',
            [
                'alias'    => 'profile_image',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL AND resource="profile-image"'
                ]
            ]
        );

        $this->hasOne(
            'id',
            Files::class,
            'resource_id',
            [
                'alias'    => 'sig',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL AND resource="sig"'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Logs::class,
            'resource_id',
            [
                'alias'    => 'logs',
                'reusable' => true,
                'params'   => [
                    'order' => 'created_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            Orders::class,
            'customer_id',
            [
                'alias'    => 'orders',
                'reusable' => true
            ]
        );

        $this->hasMany(
            'id',
            StatementItems::class,
            'shareholder_id',
            [
                'alias'    => 'dividends',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'processed_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            StatementItems::class,
            'employee_id',
            [
                'alias'    => 'payments',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'processed_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            StatementItems::class,
            'expenses_employee_id',
            [
                'alias'    => 'expenses',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                    'order' => 'processed_at DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            UserTaxCodes::class,
            'user_id',
            [
                'alias'    => 'tax_codes',
                'reusable' => true,
                'params'   => [
                    'conditions' => 'deleted_at IS NULL',
                ]
            ]
        );
    }

    public function dividendsTaxYearAmount($tax_year)
    {
        $params = [
            'start' => $tax_year->tax_year_start,
            'end' => $tax_year->tax_year_end,
            'shareholder_id' => $this->id
        ];
        
        $statement_items = new StatementItems();
        $statement_items_table = $statement_items->getSource();

        $query = " SELECT
            SUM(`out`) as total
        FROM 
            $statement_items_table
        WHERE 
            deleted_at IS NULL AND 
            shareholder_id = :shareholder_id AND 
            (processed_at BETWEEN :start AND :end)";

        $result =
            (
                new \Phalcon\Mvc\Model\Resultset\Simple(
                    null,
                    $statement_items,
                    $statement_items->getReadConnection()->query(rtrim($query, ','), $params)
                )
            )->toArray();

        return !empty($result[0]) ? floatval($result[0]['total']) : 0.00;
    }

    public function expensesTaxYearAmount($tax_year)
    {
        $params = [
            'start' => $tax_year->tax_year_start,
            'end' => $tax_year->tax_year_end,
            'expenses_employee_id' => $this->id
        ];
        
        $statement_items = new StatementItems();
        $statement_items_table = $statement_items->getSource();

        $query = " SELECT
            SUM(`out`) as total
        FROM 
            $statement_items_table
        WHERE 
            deleted_at IS NULL AND 
            expenses_employee_id = :expenses_employee_id AND 
            (processed_at BETWEEN :start AND :end)";

        $result =
            (
                new \Phalcon\Mvc\Model\Resultset\Simple(
                    null,
                    $statement_items,
                    $statement_items->getReadConnection()->query(rtrim($query, ','), $params)
                )
            )->toArray();

        return !empty($result[0]) ? floatval($result[0]['total']) : 0.00;
    }

    public function getAddress()
    {
        $string = $this->address_line_1;
        if ($this->address_line_2) {
            $string .= "\n" . $this->address_line_2;
        }
        $string .= "\n" . $this->town;
        $string .= "\n" . $this->county;
        $string .= "\n" . $this->country;
        $string .= "\n" . $this->postcode;
        
        return $string;
    }

    public function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getGroup()
    {
        if ($group = GroupsController::isSystem($this->group_id)) {
            return $group;
        } else {
            $this->hasOne(
                'group_id',
                Groups::class,
                'id',
                [
                    'alias'    => 'group',
                    'reusable' => true,
                ]
            );
        }
    }

    public function getInitials()
    {
        return substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1);
    }

    public function getDividendsAmount()
    {
        $params = [
            'shareholder_id' => $this->id
        ];

        $statement_items = new StatementItems();
        $statement_items_table = $statement_items->getSource();

        $query = " SELECT
            SUM(`out`) as total
        FROM 
            $statement_items_table
        WHERE 
            deleted_at IS NULL AND 
            shareholder_id = :shareholder_id";

        $result =
            (
                new \Phalcon\Mvc\Model\Resultset\Simple(
                    null,
                    $statement_items,
                    $statement_items->getReadConnection()->query(rtrim($query, ','), $params)
                )
            )->toArray();

        return !empty($result[0]) ? floatval($result[0]['total']) : 0.00;
    }

    public function getDividendsCurrentYearAmount()
    {
        $tax_year = $this->getCurrentTaxYear();

        $params = [
            'start' => $tax_year->tax_year_start,
            'end' => $tax_year->tax_year_end,
            'shareholder_id' => $this->id
        ];
        
        $statement_items = new StatementItems();
        $statement_items_table = $statement_items->getSource();

        $query = " SELECT
            SUM(`out`) as total
        FROM 
            $statement_items_table
        WHERE 
            deleted_at IS NULL AND 
            shareholder_id = :shareholder_id AND 
            (processed_at BETWEEN :start AND :end)";

        $result =
            (
                new \Phalcon\Mvc\Model\Resultset\Simple(
                    null,
                    $statement_items,
                    $statement_items->getReadConnection()->query(rtrim($query, ','), $params)
                )
            )->toArray();

        return !empty($result[0]) ? floatval($result[0]['total']) : 0.00;
    }

    public function getExpensesCurrentYearAmount()
    {
        $tax_year = $this->getCurrentTaxYear();

        $params = [
            'start' => $tax_year->tax_year_start,
            'end' => $tax_year->tax_year_end,
            'expenses_employee_id' => $this->id
        ];
        
        $statement_items = new StatementItems();
        $statement_items_table = $statement_items->getSource();

        $query = " SELECT
            SUM(`out`) as total
        FROM 
            $statement_items_table
        WHERE 
            deleted_at IS NULL AND 
            expenses_employee_id = :expenses_employee_id AND 
            (processed_at BETWEEN :start AND :end)";

        $result =
            (
                new \Phalcon\Mvc\Model\Resultset\Simple(
                    null,
                    $statement_items,
                    $statement_items->getReadConnection()->query(rtrim($query, ','), $params)
                )
            )->toArray();

        return !empty($result[0]) ? floatval($result[0]['total']) : 0.00;
    }

    public function getPaymentsCurrentYearAmount()
    {
        $tax_year = $this->getCurrentTaxYear();

        $params = [
            'start' => $tax_year->tax_year_start,
            'end' => $tax_year->tax_year_end,
            'employee_id' => $this->id
        ];
        
        $statement_items = new StatementItems();
        $statement_items_table = $statement_items->getSource();

        $query = " SELECT
            SUM(`out`) as total
        FROM 
            $statement_items_table
        WHERE 
            deleted_at IS NULL AND 
            employee_id = :employee_id AND 
            (processed_at BETWEEN :start AND :end)";

        $result =
            (
                new \Phalcon\Mvc\Model\Resultset\Simple(
                    null,
                    $statement_items,
                    $statement_items->getReadConnection()->query(rtrim($query, ','), $params)
                )
            )->toArray();

        return !empty($result[0]) ? floatval($result[0]['total']) : 0.00;
    }

    public function paymentsTaxYearAmount($tax_year)
    {
        $params = [
            'start' => $tax_year->tax_year_start,
            'end' => $tax_year->tax_year_end,
            'employee_id' => $this->id
        ];
        
        $statement_items = new StatementItems();
        $statement_items_table = $statement_items->getSource();

        $query = " SELECT
            SUM(`out`) as total
        FROM 
            $statement_items_table
        WHERE 
            deleted_at IS NULL AND 
            employee_id = :employee_id AND 
            (processed_at BETWEEN :start AND :end)";

        $result =
            (
                new \Phalcon\Mvc\Model\Resultset\Simple(
                    null,
                    $statement_items,
                    $statement_items->getReadConnection()->query(rtrim($query, ','), $params)
                )
            )->toArray();

        return !empty($result[0]) ? floatval($result[0]['total']) : 0.00;
    }

    public function taxCode($tax_year)
    {
        return UserTaxCodes::findFirst([
            'conditions' => 'user_id = :user_id: AND tax_year_id = :tax_year_id: AND deleted_at IS NULL',
            'bind' => [
                'user_id' => $this->id,
                'tax_year_id' => $tax_year->id
            ]
        ]);
    }
}
