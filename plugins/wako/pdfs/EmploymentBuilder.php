<?php

/**
 * Employment builder.
 *
 * @package     Kytschi\Wako\PDFs\EmploymentBuilder
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Wako\PDFs;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\NumberHelper;
use Kytschi\Tengu\Traits\Core\Files;
use Mpdf\Mpdf;
use Phalcon\Encryption\Security\Random;

class EmploymentBuilder extends ControllerBase
{
    use Files;

    public function build($return, $model)
    {
        $filename = ($this->di->getConfig())->application->tmpDir . (new Random())->uuid();
        shell_exec(
            'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=' .
            $filename . ' '  .
            ($this->di->getConfig())->application->pluginsDir . 'wako/pdfs/SA102_2022.pdf'
        );
                
        $pdf = new \Mpdf\Mpdf([
            'tempDir' => ($this->di->getConfig())->application->tmpDir
        ]);
                        
        $pages = $pdf->SetSourceFile($filename);

        $page = $pdf->ImportPage(1);

        $pdf->UseTemplate($page);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);

        $return->decryptData();

        /**
         * Your name.
         */
        $pdf->SetXY(24, 41);
        $pdf->WriteCell(75, 5, $return->your_name, '', '', 'L', true);

        /**
         * UTR
         */
        $x = 115;
        $splits = str_split($return->utr);
        foreach ($splits as $key => $string) {
            $pdf->SetXY($x, 41);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            if ($key == 4) {
                $x += 8;
            } else {
                $x += 5.5;
            }
        }

        /**
         * Pay
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->payment_amount)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 78);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }
        
        /**
         * Tax off payment
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->payment_tax_off_amount)));
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
         * Tips
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->payment_tips_amount)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 110);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }
        
        /**
         * Paye ref
         */
        $x = 24;
        $splits = str_split($return->paye_tax_ref);
        foreach ($splits as $key => $string) {
            if ($key == 3) {
                $x += 5.5;
                continue;
            }
            $pdf->SetXY($x, 125);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x += 5.5;
        }
        
        /**
         * Employers name
         */
        $pdf->SetXY(24, 141);
        $pdf->WriteCell(75, 5, $return->employer_name, '', '', 'L', true);

        /**
         * Director
         */
        if ($return->director) {
            $pdf->SetXY(115, 74);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Director ceased date
         */
        $x = 115;
        if ($return->director_ceased_date) {
            $splits = str_split(DateHelper::pretty($return->director_ceased_date, false));
            foreach ($splits as $key => $string) {
                if ($string == '/') {
                    continue;
                }
                if ($key == 3) {
                    $x += 2.5;
                } elseif ($key == 6) {
                    $x += 2.5;
                }
                $pdf->SetXY($x, 94);
                $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
                $x += 5.5;
            }
        }

        /**
         * Company closed
         */
        if ($return->company_closed) {
            $pdf->SetXY(115, 114);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Off payroll
         */
        if ($return->off_payroll) {
            $pdf->SetXY(115, 134);
            $pdf->WriteCell(3, 4, 'X', '', '', 'L', true);
        }

        /**
         * Company cars
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->company_cars)));
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
         * Fuel for company cars
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->fuel_for_company_cars)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 196.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Private medical insurance
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->private_medical_insurance)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 212);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Vouchers amount
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->vouchers_amount)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 228);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Goods amount
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->goods_amount)));
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
         * Accommodation amount
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->accommodation_amount)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 196.5);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Other benefits
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->other_benefits)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 217);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Expenses payments
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->expenses_payments)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 233);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Business travel amount
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->business_travel_amount)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 258);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Fixed deductions amount
         */
        $x = 81.5;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->fixed_deductions_amount)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 274);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Professional fees
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->professional_fees)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 258);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        /**
         * Other expenses amount
         */
        $x = 173;
        $splits = array_reverse(str_split(NumberHelper::to2DP($return->other_expenses_amount)));
        foreach ($splits as $key => $string) {
            if ($string == '.') {
                $x -= 3;
                continue;
            }
            $pdf->SetXY($x, 274);
            $pdf->WriteCell(3, 4, $string, '', '', 'L', true);
            $x -= 5.5;
        }

        $pdf->Output($filename, \Mpdf\Output\Destination::FILE);

        file_put_contents(
            $return->file->location,
            self::encrypt(file_get_contents($filename))
        );
        shell_exec('rm ' . $filename);
    }
}
