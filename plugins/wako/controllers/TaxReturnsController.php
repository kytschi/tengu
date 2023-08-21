<?php

/**
 * Tax returns controller.
 *
 * @package     Kytschi\Wako\Controllers\TaxReturnsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
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

namespace Kytschi\Wako\Controllers;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\NumberHelper;
use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Api;
use Kytschi\Tengu\Traits\Core\Currency;
use Kytschi\Tengu\Models\Core\Projects;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Wako\Models\TaxReturns;
use Kytschi\Wako\Models\TaxReturnEmployments;
use Kytschi\Wako\Models\TaxReturnSelfAssessment;
use Kytschi\Wako\PDFs\EmploymentBuilder;
use Kytschi\Wako\PDFs\SelfAssessmentBuilder;
use Kytschi\Wako\Traits\TaxYear;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class TaxReturnsController extends ControllerBase
{
    use Currency;
    use Files;
    use Form;
    use Logs;
    use Notes;
    use Pagination;
    use Tags;
    use TaxYear;

    public $access = [
        'administrator',
        'super-user',
        'finance-manager'
    ];

    public $global_url = '/tax-returns';
    public $resource = 'tax-return';

    public $types = [
        'self-assessment',
        'corporation tax',
    ];

    private $include_types = '"employee", "director"';
    
    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->fms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Creating a tax return');

        return $this->view->partial(
            'wako/tax_returns/add',
            [
                'tax_years' => $this->getTaxYears(),
                'types' => $this->types,
                'users' => (new Users())->find([
                    'conditions' => 'type IN(' . $this->include_types . ')',
                ])
            ]
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();
        $this->secure($this->access);

        $model = (new TaxReturns())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->softDelete();

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormDeleted('Tax return has been deleted');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (\Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function editAction()
    {
        $this->secure($this->access);

        $model = (new TaxReturns())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->setPageTitle('Editing the tax return');
        $this->setPageSubTitle(
            'Selected tax year&nbsp;<strong>' . $model->tax_year->code . '&nbsp;' .
            DateHelper::pretty($model->tax_year->tax_year_start, false) . '</strong>&nbsp;to&nbsp;<strong>' .
            DateHelper::pretty($model->tax_year->tax_year_end, false) . '</strong>'
        );

        return $this->view->partial(
            'wako/tax_returns/edit',
            [
                'data' => $model,
                'tax_years' => $this->getTaxYears(),
                'types' => $this->types,
                'users' => (new Users())->find([
                    'conditions' => 'type IN(' . $this->include_types . ')',
                ])
            ]
        );
    }

    public function indexAction()
    {
        $this->clearFormData();
        $this->orderDir = 'DESC';

        $this->secure($this->access);
        $this->setPageTitle('Tax returns');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'tax_year_id',
                'created_at',
                'status'
            ],
            'created_at'
        );


        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(TaxReturns::class)
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'name' => '%' . $this->search . '%',
                'search_tags' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere('name LIKE :name: OR search_tags LIKE :search_tags:');
        }

        $tax_year = $this->getCurrentTaxYear();
        $this->setPageSubTitle(
            'Selected tax year&nbsp;<strong>' . $tax_year->code . '&nbsp;' .
            DateHelper::pretty($tax_year->tax_year_start, false) . '</strong>&nbsp;to&nbsp;<strong>' .
            DateHelper::pretty($tax_year->tax_year_end, false) . '</strong>'
        );

        if (!empty($this->filters)) {
            $iLoop = 1;
            foreach ($this->filters as $filter => $value) {
                if (empty($value)) {
                    continue;
                }
                switch ($filter) {
                    case 'status':
                        $builder->andWhere('status LIKE :status_' . $iLoop . ':');
                        $params['status_' . $iLoop] = $value;
                        $iLoop++;
                        break;
                    case 'taxable':
                        $builder->andWhere('taxable = :taxable:');
                        $params['taxable'] = ($value == 'yes' ? 1 : 0);
                        $iLoop++;
                        break;
                    case 'year':
                        $builder->andWhere('issued_on BETWEEN :start: AND :end:');
                        $params['start'] = $tax_year->tax_year_start;
                        $params['end'] = $tax_year->tax_year_end;
                        $iLoop++;
                        break;
                }
            }
        }

        $builder->setBindParams($params);

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        return $this->view->partial(
            'wako/tax_returns/index',
            [
                'data' => $paginator->paginate(),
                //'stats' => $this->stats($tax_year->tax_year_start, $tax_year->tax_year_end),
                'tax_year' => $tax_year,
                'years' => $this->getTaxYears()
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new TaxReturns())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->recover();

            $this->addLog(
                $this->resource,
                $model->id,
                'warning',
                'Recovered by ' . $this->getUserFullName()
            );

            $url = $this->global_url . '/edit/' . $model->id;
            if (!empty($_GET['from'])) {
                $url = urldecode($_GET['from']);
            }

            $this->saveFormUpdated('Tax return has been recovered');
            $this->redirect(UrlHelper::backend(rtrim($url, '/')));
        } catch (\Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function saveAction()
    {
        $this->secure($this->access);

        try {
            $this->validate();

            $model = $this->setData(new TaxReturns());
            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to add the tax return',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);

            switch ($model->type) {
                case 'self-assessment':
                    $pdf = ($this->di->getConfig())->application->pluginsDir .
                        'wako/pdfs/SA100_tax_return__2022_.pdf';
                    $return = new TaxReturnSelfAssessment([
                        'tax_return_id' => $model->id
                    ]);
                    if ($return->save() === false) {
                        throw new SaveException(
                            'Failed to create the tax return self assessment data',
                            $return->getMessages()
                        );
                    }

                    $this->addFile(
                        $return->id,
                        $pdf,
                        $this->resource,
                        $model->type,
                        '',
                        false,
                        true
                    );

                    $pdf = ($this->di->getConfig())->application->pluginsDir .
                        'wako/pdfs/SA102_2022.pdf';
                    $this->addFile(
                        $return->id,
                        $pdf,
                        $this->resource . '-employment',
                        $model->type . ' employment return',
                        '',
                        false,
                        true
                    );
                    break;
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved('Tax return has been saved');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/create'));
        } catch (Exception $err) {
            if (!empty($model)) {
                $model->delete();
            }

            if (!empty($board)) {
                $board->delete();
            }

            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function setData($model)
    {
        $model->user_id = $_POST['user_id'];
        $model->type = $_POST['type'];
        $model->tax_year_id = $_POST['tax_year_id'];

        $model = $this->addSearchTags($model);

        return $model;
    }

    public function stats($start, $end)
    {
        $model = new TaxReturns();
        $table = $model->getSource();

        $params = [
            'outgoing_taxable_start' => $start,
            'outgoing_taxable_end' => $end,
            'outgoing_vat_start' => $start,
            'outgoing_vat_end' => $end
        ];

        $search = '';
        if (!empty($this->search)) {
            $params = array_merge(
                $params,
                [
                    'name' => '%' . $this->search . '%',
                    'search_tags' => '%' . $this->search . '%'
                ]
            );
            $search = ' AND (name LIKE :name OR search_tags LIKE :search_tags)';
        }

        $query = "SELECT 
        (
            SELECT
                SUM(amount)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND
                taxable = 1 AND 
                (issued_on BETWEEN :outgoing_taxable_start AND :outgoing_taxable_end)
                $search
        ) AS outgoing_taxable,
        (
            SELECT
                SUM(vat)
            FROM 
                $table
            WHERE 
                deleted_at IS NULL AND
                taxable = 1 AND 
                (issued_on BETWEEN :outgoing_vat_start AND :outgoing_vat_end)
                $search
        ) AS outgoing_vat";

        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','), $params)
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new TaxReturns())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $url = $this->global_url . '/edit/' . $model->id;

        try {
            $this->validate();
            $model = $this->setData($model);
            
            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the tax return',
                    $model->getMessages()
                );
            }
            
            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);
            
            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->updateReturnEmployment($model);

            switch ($model->type) {
                case 'self-assessment':
                    $this->updateReturnSelfAssessment($model);
                    break;
            }

            $this->saveFormUpdated('Tax return has been updated');

            $this->clearFormData();
            $this->redirect(UrlHelper::backend($url));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    private function updateReturnEmployment($model)
    {
        $return = $model->employment_return;
        $save = false;
        if (empty($return)) {
            $return = new TaxReturnEmployments(['tax_return_id' => $model->id]);
            $save = true;
        }

        $strings = [
            'utr',
            'your_name',
            'paye_tax_ref',
            'employer_name'
        ];

        foreach ($strings as $key) {
            $return->$key = !empty($_POST[$key]) ? $_POST[$key] : '';
        }

        $dates = [
            'director_ceased_date'
        ];

        foreach ($dates as $key) {
            $return->$key = !empty($_POST[$key]) ? DateHelper::sql($_POST[$key], false) : null;
        }
        
        $bools = [
            'employment',
            'self_employment',
            'partnership',
            'property',
            'foreign',
            'trusts',
            'capital_gains',
            'computation_provided',
            'residence',
            'additional_info',
            'more_pages',
            'director',
            'company_closed',
            'off_payroll'
        ];

        foreach ($bools as $key) {
            $return->$key = !empty($_POST[$key]) ? true : false;
        }

        $ints = [
            'employment_page_no',
            'self_employment_page_no',
            'partnership_page_no'
        ];

        foreach ($ints as $key) {
            $return->$key = !empty($_POST[$key]) ? intval($_POST[$key]) : 0;
        }
        
        $currency = [
            'payment_amount',
            'payment_tax_off_amount',
            'payment_tips_amount',
            'company_cars',
            'fuel_for_company_cars',
            'private_medical_insurance',
            'vouchers_amount',
            'goods_amount',
            'accommodation_amount',
            'other_benefits',
            'expenses_payments',
            'business_travel_amount',
            'fixed_deductions_amount',
            'professional_fees',
            'other_expenses_amount',
            'taxed_uk_interest'
        ];

        foreach ($currency as $key) {
            $return->$key = !empty($_POST[$key]) ? NumberHelper::fromCurrency($_POST[$key]) : 0;
        }

        if ($save) {
            if ($return->save() === false) {
                throw new SaveException(
                    'Failed to save the tax return employment data',
                    $return->getMessages()
                );
            }
        } elseif ($return->update() === false) {
            throw new SaveException(
                'Failed to update the tax return employment data',
                $return->getMessages()
            );
        }

        (new EmploymentBuilder())->build($return, $model);
    }

    private function updateReturnSelfAssessment($model)
    {
        $return = $model->return_data;
        $save = false;
        if (empty($return)) {
            $return = new TaxReturnSelfAssessment(['tax_return_id' => $model->id]);
            $save = true;
        }

        $strings = [
            'utr',
            'nino',
            'employee_ref',
            'address',
            'phone',
            'national_insurance',
            'paye_tax_ref',
            'employer_name',
            'your_name',
            'registered_blind_name',
            'spouse_first_name',
            'spouse_last_name',
            'spouse_national_insurance_no',
            'name_of_bank',
            'name_of_account_holder',
            'sort_code',
            'account_number',
            'society_ref',
            'nominee_address',
            'nominee_postcode',
            'tax_advisors_name',
            'tax_advisors_phone',
            'tax_advisors_address',
            'tax_advisors_postcode',
            'tax_advisors_ref',
            'other_info',
            'signed_on_behalf',
            'signed_on_behalf_name',
            'signed_on_behalf_your_name',
            'signed_on_behalf_your_address',
            'signed_on_behalf_your_postcode',
            'description_of_income',
        ];

        foreach ($strings as $key) {
            $return->$key = !empty($_POST[$key]) ? $_POST[$key] : '';
        }

        $dates = [
            'director_ceased_date',
            'dob',
            'child_benefits_date',
            'spouse_dob',
            'marriage_date',
        ];

        foreach ($dates as $key) {
            $return->$key = !empty($_POST[$key]) ? DateHelper::sql($_POST[$key], false) : null;
        }
        
        $bools = [
            'employment',
            'self_employment',
            'partnership',
            'property',
            'foreign',
            'trusts',
            'capital_gains',
            'computation_provided',
            'residence',
            'additional_info',
            'more_pages',
            'registered_blind',
            'spouse_surplus_allowancev',
            'your_surplus_allowance',
            'student_loan_repayment',
            'student_loan_replayment_amount',
            'postgraduate_loan_replayment_amount',
            'owe_tax',
            'owe_tax_on_savings',
            'no_society_account',
            'nominees_name',
            'nominee_is_tax_adviser',
            'provisional_figures',
            'convid_support_received',
            'additional_pages',
        ];

        foreach ($bools as $key) {
            $return->$key = !empty($_POST[$key]) ? true : false;
        }

        $ints = [
            'employment_page_no',
            'self_employment_page_no',
            'partnership_page_no',
            'child_benefits_no_children',
        ];

        foreach ($ints as $key) {
            $return->$key = !empty($_POST[$key]) ? intval($_POST[$key]) : 0;
        }

        if ($return->employment && !$return->employment_page_no) {
            $return->employment_page_no = 1;
        }

        if ($return->self_employment && !$return->self_employment_page_no) {
            $return->self_employment_page_no = 1;
        }

        if ($return->partnership && !$return->partnership_page_no) {
            $return->partnership_page_no = 1;
        }
        
        $currency = [
            'taxed_uk_interest',
            'untaxed_uk_interest',
            'untaxed_foreign_interest',
            'dividends_uk_companies',
            'other_dividends',
            'foreign_dividends',
            'tax_taken_off_foreign_dividends',
            'state_pension',
            'state_pension_lump',
            'tax_off_state_pension_lump',
            'other_pensions',
            'tax_off_other_pensions',
            'tax_benefits',
            'tax_taken_off_benefits',
            'jobseekers_allowance',
            'tax_other_pensions_benefits',
            'other_taxable_income',
            'total_amount_allowable_expenses',
            'tax_off_other_lump',
            'benefit_from_pre_assets',
            'payment_to_registered_pensions',
            'payments_to_retirement_annuity',
            'payments_to_employer_scheme',
            'payments_to_overseas_pension_scheme',
            'gift_aid_payments',
            'one_off_payments',
            'gift_aid_payments_treated',
            'gift_aid_payments_treated_2',
            'qualifying_shares',
            'qualifying_land_gifted_charity',
            'qualifying_investments',
            'gift_aid_payments_non_uk_charity',
            'child_benefits_amount',
            'convid_incorrectly_claimed',
            'convid_incorrectly_claimed_seiss',
            'tax_refunded_amount',
        ];

        foreach ($currency as $key) {
            $return->$key = !empty($_POST[$key]) ? NumberHelper::fromCurrency($_POST[$key]) : 0;
        }

        if ($save) {
            if ($return->save() === false) {
                throw new SaveException(
                    'Failed to save the tax return self assessment data',
                    $return->getMessages()
                );
            }
        } elseif ($return->update() === false) {
            throw new SaveException(
                'Failed to update the tax return self assessment data',
                $return->getMessages()
            );
        }

        (new SelfAssessmentBuilder())->build($return, $model);
    }

    public function validate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            'user_id',
            new PresenceOf(
                [
                    'message' => 'The employee is required',
                ]
            )
        );

        $validation->add(
            'type',
            new PresenceOf(
                [
                    'message' => 'The type is required',
                ]
            )
        );

        $validation->add(
            'tax_year_id',
            new PresenceOf(
                [
                    'message' => 'The tax year is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
