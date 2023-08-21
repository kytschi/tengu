<?php

/**
 * Self Assessment builder.
 *
 * @package     Kytschi\Wako\PDFs\SelfAssessmentBuilder
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

namespace Kytschi\Wako\PDFs;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\NumberHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Files;
use Mpdf\Mpdf;
use Phalcon\Encryption\Security\Random;

class SelfAssessmentBuilder extends ControllerBase
{
    use Files;

    public function build($return, $model)
    {
        $filename = ($this->di->getConfig())->application->tmpDir . (new Random())->uuid();
        shell_exec(
            'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=' .
            $filename . ' '  .
            ($this->di->getConfig())->application->pluginsDir . 'wako/pdfs/SA100_tax_return__2022_.pdf'
        );
        
        $pdf = new \Mpdf\Mpdf([
            'tempDir' => ($this->di->getConfig())->application->tmpDir
        ]);
                        
        $pages = $pdf->SetSourceFile($filename);

        $page = $pdf->ImportPage(3);

        $pdf->UseTemplate($page);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);

        $return->decryptData();

        /**
         * Your address.
         */
        $y = 47;
        $strings = explode("\n", $return->address);
        foreach ($strings as $string) {
            $pdf->SetXY(109, $y);
            $pdf->WriteCell(75, 5, $string, '', '', 'L', true);
            $y += 4;
        }

        /**
         * UTR
         */
        $pdf->SetXY(26, 34.5);
        $pdf->WriteCell(35, 4, $return->utr, '', '', 'L', true);

        /**
         * Employer reference.
         */
        $pdf->SetXY(47, 43.5);
        $pdf->WriteCell(35, 4, $return->employee_ref, '', '', 'L', true);

        /**
         * Date.
         */
        $pdf->SetXY(24, 50.5);
        $pdf->WriteCell(35, 4, DateHelper::pretty(null, false, true), '', '', 'L', true);

        /**
         * Date of birth
         */
        $x = 24;
        $splits = str_split(DateHelper::pretty($return->dob, false));
        foreach ($splits as $key => $string) {
            if ($string == '/') {
                continue;
            }
            if ($key == 3) {
                $x += 2.5;
            } elseif ($key == 6) {
                $x += 2.5;
            }
            $pdf->SetXY($x, 244);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += 5.5;
        }

        /**
         * Date of birth
         */
        if ($return->address_date) {
            $x = 24;
            $splits = str_split(DateHelper::pretty($return->address_date, false, false, ''));
            foreach ($splits as $key => $string) {
                if ($string == '/') {
                    continue;
                }
                if ($key == 3) {
                    $x += 2.5;
                } elseif ($key == 6) {
                    $x += 2.5;
                }
                $pdf->SetXY($x, 275);
                $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
                $x += 5.5;
            }
        }

        /**
         * Your phone number
         */
        if ($return->phone) {
            $x = 115;
            $splits = str_split($return->phone);
            foreach ($splits as $key => $string) {
                $pdf->SetXY($x, 240);
                $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
                $x += 5.5;
            }
        }

        /**
         * National insurance no.
         */
        $x = 115;
        $splits = str_split($return->national_insurance);
        foreach ($splits as $key => $string) {
            if ($key == 2 || $key == 4 || $key == 6 || $key == 8) {
                $x += 2.5;
            }
            $pdf->SetXY($x, 262);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += 5.5;
        }

        $page = $pdf->ImportPage(4);
        $pdf->AddPage('P');
        $pdf->UseTemplate($page);

        /**
         * Employment.
         */
        if ($return->employment) {
            $pdf->SetXY(32, 82);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        } else {
            $pdf->SetXY(49.5, 82);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Employment page no.
         */
        $pdf->SetXY(75, 82);
        $pdf->WriteCell(3, 4, (string) $return->employment_page_no, '', '', 'L', true);

        /**
         * Self employment.
         */
        if ($return->self_employment) {
            $pdf->SetXY(32, 142.5);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        } else {
            $pdf->SetXY(49.5, 142.5);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Self employment page no.
         */
        $pdf->SetXY(74.5, 142.5);
        $pdf->WriteCell(3, 4, (string) $return->self_employment_page_no, '', '', 'L', true);

        /**
         * Partnership.
         */
        if ($return->partnership) {
            $pdf->SetXY(32, 174.5);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        } else {
            $pdf->SetXY(49.5, 174.5);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Partnership page no.
         */
        $pdf->SetXY(74.5, 174.5);
        $pdf->WriteCell(3, 4, (string) $return->partnership_page_no, '', '', 'L', true);

        /**
         * UK property.
         */
        if ($return->property) {
            $pdf->SetXY(32, 214);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        } else {
            $pdf->SetXY(49.5, 214);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Foreign.
         */
        if ($return->foreign) {
            $pdf->SetXY(32, 272);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        } else {
            $pdf->SetXY(49.5, 272);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Trusts.
         */
        if ($return->trusts) {
            $pdf->SetXY(123, 71.5);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        } else {
            $pdf->SetXY(141, 71.5);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Capital gains.
         */
        if ($return->capital_gains) {
            $pdf->SetXY(123, 119);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        } else {
            $pdf->SetXY(141, 119);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Capital gains computation provided.
         */
        if ($return->computation_provided) {
            $pdf->SetXY(188, 119);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Residence, remittance basis etc.
         */
        if ($return->residence) {
            $pdf->SetXY(123, 162);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        } else {
            $pdf->SetXY(141, 162);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Additional information.
         */
        if ($return->additional_info) {
            $pdf->SetXY(123, 205);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        } else {
            $pdf->SetXY(141, 205);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * More pages.
         */
        if ($return->more_pages) {
            $pdf->SetXY(123, 248);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        } else {
            $pdf->SetXY(141, 248);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        $page = $pdf->ImportPage(5);
        $pdf->AddPage('P');
        $pdf->UseTemplate($page);

        /**
         * Taxed UK interest.
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->taxed_uk_interest)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 49);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Untaxed UK interest.
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->untaxed_uk_interest)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 69);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Untaxed foreign interest.
         */
        $x = 60;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->untaxed_foreign_interest)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 89);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Dividends UK companies.
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->dividends_uk_companies)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 109);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Other dividends.
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->other_dividends)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 44.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Foreign dividends.
         */
        $x = 151;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->foreign_dividends)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 69);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Tax taken off foreign dividends.
         */
        $x = 151;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->tax_taken_off_foreign_dividends)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 85);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * State Pension.
         */
        $x = 65;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->state_pension)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 141);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * State Pension lump sum.
         */
        $x = 65;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->state_pension_lump)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 161);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Tax off state pension lump
         */
        $x = 65;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->tax_off_state_pension_lump)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 176.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Other pensions
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->other_pensions)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 201);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Tax taken off box 11
         */
        $x = 178.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->tax_off_other_pensions)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 136.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Taxable Incapacity Benefit and contribution-based Employment and Support Allowance
         */
        $x = 156.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->tax_benefits)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 156.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Tax taken off Incapacity Benefit in box 13
         */
        $x = 156.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->tax_taken_off_benefits)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 171.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Jobseeker’s Allowance
         */
        $x = 156.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->jobseekers_allowance)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 187.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Total of any other taxable State Pensions and benefits
         */
        $x = 156.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->tax_other_pensions_benefits)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 203.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Other taxable income
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->other_taxable_income)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 245);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Total amount allowable expenses
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->total_amount_allowable_expenses)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 260.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Tax off other lump
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->tax_off_other_lump)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 276.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Benefit from pre assets
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->benefit_from_pre_assets)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 240.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Benefit from pre assets
         */
        $y = 265;
        $start_x = 115;
        $x = $start_x;
        $space = 2;
        $max_width = $start_x + 70;
        $splits = explode(' ', $return->description_of_income);
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, $y);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += $pdf->GetStringWidth($string) + $space;
            if ($x >= $max_width) {
                $x = $start_x;
                $y += 6;
            }
        }

        $page = $pdf->ImportPage(6);
        $pdf->AddPage('P');
        $pdf->UseTemplate($page);

        /**
         * Payment to registered pensions
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->payment_to_registered_pensions)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 72);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Payments to retirement annuity
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->payments_to_retirement_annuity)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 94);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Payments to employer scheme
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->payments_to_employer_scheme)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 68);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Payments to overseas pension scheme
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->payments_to_overseas_pension_scheme)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 94);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Gift Aid payments made in the year to 5 April 202
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->gift_aid_payments)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 121);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Total of any ‘one-off’ payment
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->one_off_payments)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 139);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Gift Aid payments made in the year to 5 April 2022 but treated as if made in the year to 5 April 2021
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->gift_aid_payments_treated)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 161);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Gift Aid payments made in the year to 5 April 2022 but to be treated as if made in the year to 5 April 2022
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->gift_aid_payments_treated_2)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 183);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Value of qualifying shares or securities gifted to charity
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->qualifying_shares)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 121);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Value of qualifying land and buildings gifted to charity
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->qualifying_land_gifted_charity)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 139);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Value of qualifying investments gifted to non-UK charities in boxes 9 and 10
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->qualifying_investments)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 161);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Gift Aid payments to non-UK charities in box 5
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->gift_aid_payments_non_uk_charity)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 179);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * If you’re registered blind, or severely sight impaired, and your name is on a
         * local authority or other register
         */
        if ($return->registered_blind) {
            $pdf->SetXY(24, 219);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Enter the name of the local authority or other register
         */
        $y = 237;
        $start_x = 24;
        $x = $start_x;
        $space = 2;
        $max_width = $start_x + 70;
        $splits = explode(' ', $return->registered_blind_name);
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, $y);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += $pdf->GetStringWidth($string) + $space;
            if ($x >= $max_width) {
                $x = $start_x;
                $y += 6;
            }
        }

        /**
         * If you want your spouse’s, or civil partner’s.
         */
        if ($return->spouse_surplus_allowance) {
            $pdf->SetXY(115, 215);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * If you want your spouse, or civil partner, to have your surplus allowance.
         */
        if ($return->your_surplus_allowance) {
            $pdf->SetXY(115, 237);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        $page = $pdf->ImportPage(7);
        $pdf->AddPage('P');
        $pdf->UseTemplate($page);

        /**
         * If you’ve received notification from Student Loans Company that your repayment.
         */
        if ($return->student_loan_repayment) {
            $pdf->SetXY(24, 57.5);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * If your employer has deducted Student Loan repayments enter the amount deducted.
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->student_loan_replayment_amount)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 44.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * If your employer has deducted Postgraduate Loan repayments enter the amount deducted
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->postgraduate_loan_replayment_amount)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 64.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Enter the total amount of Child Benefit you and your partner got for the year to 5 April 2022
         */
        $x = 65;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->child_benefits_amount)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 119);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Enter the number of children you and your partner got Child Benefit for on 5 April 2022
         */
        $x = 29;
        $splits = array_reverse(str_split((string) $return->child_benefits_no_children));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 139);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Enter the date that you and your partner stopped getting all
         * Child Benefit payments if this was before 6 April 2022.
         */
        if ($return->child_benefits_date) {
            $x = 115;
            $splits = str_split(DateHelper::pretty($return->child_benefits_date, false));
            foreach ($splits as $key => $string) {
                if ($string == '/') {
                    continue;
                }
                if ($key == 3) {
                    $x += 2.5;
                } elseif ($key == 6) {
                    $x += 2.5;
                }
                $pdf->SetXY($x, 128);
                $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
                $x += 5.5;
            }
        }

        /**
         * Amount of HMRC coronavirus support scheme payments (other than SEISS) incorrectly claimed
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->convid_incorrectly_claimed)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 185.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Amount of SEISS payments incorrectly claimed
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->convid_incorrectly_claimed_seiss)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 181);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Your spouse or civil partner’s first name
         */
        if ($return->spouse_first_name) {
            $pdf->SetXY(24, 242);
            $pdf->WriteCell(3, 4, $return->spouse_first_name, '', '', 'L', true);
        }

        /**
         * Your spouse or civil partner’s last name
         */
        if ($return->spouse_last_name) {
            $pdf->SetXY(24, 258.5);
            $pdf->WriteCell(3, 4, $return->spouse_last_name, '', '', 'L', true);
        }

        /**
         * Your spouse or civil partner’s National Insurance number.
         */
        $x = 24;
        $splits = str_split($return->spouse_national_insurance_no);
        foreach ($splits as $key => $string) {
            if ($key == 2 || $key == 4 || $key == 6 || $key == 8) {
                $x += 2.5;
            }
            $pdf->SetXY($x, 275.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += 5.5;
        }

        /**
         * Your spouse or civil partner’s date of birth.
         */
        if ($return->spouse_dob) {
            $x = 115;
            $splits = str_split(DateHelper::pretty($return->spouse_dob, false));
            foreach ($splits as $key => $string) {
                if ($string == '/') {
                    continue;
                }
                if ($key == 3) {
                    $x += 2.5;
                } elseif ($key == 6) {
                    $x += 2.5;
                }
                $pdf->SetXY($x, 241.5);
                $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
                $x += 5.5;
            }
        }

        /**
         * Date of marriage or civil partnership.
         */
        if ($return->marriage_date) {
            $x = 115;
            $splits = str_split(DateHelper::pretty($return->marriage_date, false));
            foreach ($splits as $key => $string) {
                if ($string == '/') {
                    continue;
                }
                if ($key == 3) {
                    $x += 2.5;
                } elseif ($key == 6) {
                    $x += 2.5;
                }
                $pdf->SetXY($x, 258);
                $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
                $x += 5.5;
            }
        }

        $pdf->AddPage('P');
        $pdf->UseTemplate($pdf->ImportPage(8));

        /**
         * If you’ve had any 2021–22 Income Tax refunded or set off by us or Jobcentre Plus, enter the amount
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->tax_refunded_amount)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 80);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         *If you have not paid enough tax
         */
        if ($return->owe_tax) {
            $pdf->SetXY(24, 139.5);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * If you owe tax on savings
         */
        if ($return->owe_tax_on_savings) {
            $pdf->SetXY(115, 135.5);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Name of bank or building society
         */
        if ($return->name_of_bank) {
            $pdf->SetXY(24, 181);
            $pdf->WriteCell(3, 4, $return->name_of_bank, '', '', 'L', true);
        }

        /**
         * Name of account holder (or nominee)
         */
        $y = 197;
        $start_x = 24;
        $x = $start_x;
        $space = 2;
        $max_width = $start_x + 70;
        $splits = explode(' ', $return->name_of_account_holder);
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, $y);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += $pdf->GetStringWidth($string) + $space;
            if ($x >= $max_width) {
                $x = $start_x;
                $y += 6;
            }
        }

        /**
         * Branch sort code.
         */
        $x = 24;
        $splits = str_split(str_replace(['-', ':', ' '], '', $return->sort_code));
        foreach ($splits as $key => $string) {
            if ($key == 2 || $key == 4 || $key == 6) {
                $x += 5.5;
            }
            $pdf->SetXY($x, 218.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += 5.5;
        }

        /**
         * Account number.
         */
        $x = 24;
        $splits = str_split(str_replace(['-', ':', ' '], '', $return->account_number));
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, 234.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += 5.5;
        }

        /**
         * Building society reference number.
         */
        $x = 24;
        $splits = str_split($return->society_ref);
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, 250);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += 5.5;
        }

        /**
         * If you do not have a bank or building society account.
         */
        if ($return->no_society_account) {
            $pdf->SetXY(24, 274.5);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * If you’ve entered a nominee’s name in box 5,
         */
        if ($return->nominees_name) {
            $pdf->SetXY(115, 185);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * If your nominee is your tax adviser.
         */
        if ($return->nominee_is_tax_adviser) {
            $pdf->SetXY(115, 201);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Nominee’s address
         */
        $y = 217;
        $start_x = 115;
        $x = $start_x;
        $space = 2;
        $max_width = $start_x + 70;
        $splits = explode(' ', str_replace("\n", ', ', $return->nominee_address));
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, $y);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += $pdf->GetStringWidth($string) + $space;
            if ($x >= $max_width) {
                $x = $start_x;
                $y += 6;
            }
        }

        /**
         * Nominee’s postcode.
         */
        $x = 115;
        $splits = str_split(str_replace(' ', '', $return->nominee_postcode));
        foreach ($splits as $key => $string) {
            if ($key == 4) {
                $x += 2.5;
            }
            $pdf->SetXY($x, 245.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += 5.5;
        }

        $pdf->AddPage('P');
        $pdf->UseTemplate($pdf->ImportPage(9));

        /**
         * Your tax adviser’s name
         */
        $y = 43;
        $start_x = 24;
        $x = $start_x;
        $space = 2;
        $max_width = $start_x + 70;
        $splits = explode(' ', str_replace("\n", ', ', $return->tax_advisors_name));
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, $y);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += $pdf->GetStringWidth($string) + $space;
            if ($x >= $max_width) {
                $x = $start_x;
                $y += 6;
            }
        }

        /**
         * Their phone number
         */
        $x = 24;
        $splits = str_split(str_replace(' ', '', $return->tax_advisors_phone));
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, 66.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += 5.5;
        }

        /**
         * The first line of their address including the postcode
         */
        $y = 43;
        $start_x = 115;
        $x = $start_x;
        $space = 2;
        $max_width = $start_x + 70;
        $splits = explode(' ', str_replace("\n", ', ', $return->tax_advisors_address));
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, $y);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += $pdf->GetStringWidth($string) + $space;
            if ($x >= $max_width) {
                $x = $start_x;
                $y += 6;
            }
        }

        $pdf->SetXY(126, 61);
        $pdf->WriteCell(3, 4, $return->tax_advisors_postcode, '', '', 'L', true);

        /**
         * The reference your adviser uses for you
         */
        $x = 115;
        $splits = str_split(str_replace(' ', '', $return->tax_advisors_ref));
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, 78);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += 5.5;
        }

        /**
         * Other info
         */
        $y = 105;
        $start_x = 24;
        $x = $start_x;
        $space = 2;
        $max_width = $start_x + 150;
        $splits = explode(' ', str_replace("\n", ' ', $return->other_info));
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, $y);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += $pdf->GetStringWidth($string) + $space;
            if ($x >= $max_width) {
                $x = $start_x;
                $y += 6;
            }
        }

        $pdf->AddPage('P');
        $pdf->UseTemplate($pdf->ImportPage(10));

        /**
         * If this tax return contains provisional figures.
         */
        if ($return->provisional_figures) {
            $pdf->SetXY(24, 43.5);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * If any of your businesses received coronavirus.
         */
        if ($return->convid_support_received) {
            $pdf->SetXY(24, 78);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * If you’re enclosing separate supplementary pages.
         */
        if ($return->additional_pages) {
            $pdf->SetXY(24, 100);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Return date.
         */
        $x = 24;
        $splits = str_split(DateHelper::pretty($return->updated_at, false));
        foreach ($splits as $key => $string) {
            if ($string == '/') {
                continue;
            }
            if ($key == 3) {
                $x += 2.5;
            } elseif ($key == 6) {
                $x += 2.5;
            }
            $pdf->SetXY($x, 172.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += 5.5;
        }

        /**
         * If you’ve signed on behalf of someone else, enter the capacity.
         */
        $y = 43;
        $start_x = 115;
        $x = $start_x;
        $space = 2;
        $max_width = $start_x + 70;
        $splits = explode(' ', str_replace("\n", ', ', $return->signed_on_behalf));
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, $y);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += $pdf->GetStringWidth($string) + $space;
            if ($x >= $max_width) {
                $x = $start_x;
                $y += 6;
            }
        }

        /**
         * Enter the name of the person you’ve signed for.
         */
        $y = 66.5;
        $start_x = 115;
        $x = $start_x;
        $space = 2;
        $max_width = $start_x + 70;
        $splits = explode(' ', str_replace("\n", ' ', $return->signed_on_behalf_name));
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, $y);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += $pdf->GetStringWidth($string) + $space;
            if ($x >= $max_width) {
                $x = $start_x;
                $y += 6;
            }
        }

        /**
         * If you filled in boxes 23 and 24 enter your name.
         */
        if ($model->employee && $return->signed_on_behalf_name && $return->signed_on_behalf) {
            $y = 90.5;
            $start_x = 115;
            $x = $start_x;
            $space = 2;
            $max_width = $start_x + 70;
            $splits = explode(' ', str_replace("\n", ' ', $model->employee->full_name));
            foreach ($splits as $key => $string) {
                $pdf->SetXY($x, $y);
                $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
                $x += $pdf->GetStringWidth($string) + $space;
                if ($x >= $max_width) {
                    $x = $start_x;
                    $y += 6;
                }
            }
        }

        /**
         * If you filled in boxes 23 and 24 enter your address.
         */
        if ($model->employee && $return->signed_on_behalf_name && $return->signed_on_behalf) {
            $y = 113.5;
            $start_x = 115;
            $x = $start_x;
            $space = 2;
            $max_width = $start_x + 60;
            $address = str_replace("\n" . $model->employee->postcode, '', $model->employee->address);
            $splits = explode(' ', str_replace("\n", ', ', $address));
            foreach ($splits as $key => $string) {
                $pdf->SetXY($x, $y);
                $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
                $x += $pdf->GetStringWidth($string) + $space;
                if ($x >= $max_width) {
                    $x = $start_x;
                    $y += 6;
                }
            }

            $pdf->SetXY(128, 132);
            $pdf->WriteCell(3, 4, $model->employee->postcode, '', '', 'L', true);
        }

        /**
         * Signature image.
         */
        if ($model->employee) {
            if ($model->employee->sig) {
                $sig = ($this->di->getConfig())->application->tmpDir . (new Random())->uuid();
                file_put_contents(
                    $sig,
                    file_get_contents(
                        ($this->di->getConfig())->application->appUrl .
                        UrlHelper::backend($model->employee->sig->output_url)
                    )
                );

                list($width, $height) = getimagesize($sig);
                $rat = $width / $height;
                $height = 10;

                $type = null;
                switch ($model->employee->sig->mime_type) {
                    case 'image/jpeg':
                        $type = 'jpg';
                        break;
                    case 'image/png':
                        $type = 'png';
                        break;
                }

                if ($type) {
                    $pdf->Image(
                        $sig,
                        26,
                        150,
                        $height * $rat,
                        $height,
                        $type,
                        '',
                        true,
                        false
                    );
                }
                shell_exec('rm ' . $sig);
            }
        }


        $pdf->Output($filename, \Mpdf\Output\Destination::FILE);

        file_put_contents(
            $return->file->location,
            self::encrypt(file_get_contents($filename))
        );
        shell_exec('rm ' . $filename);
    }
}
