<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PayReportSummaryListTransformer extends TransformerAbstract
{
    public function transform($payslip_summary)
    {
        $payslips = $payslip_summary->payslip;
        $cycle_start_date = $payslip_summary->cycle_start_date;
        $disbursed_at = $payslip_summary->disbursed_at;
        $status = $payslip_summary->status;
        $total_gross = $this->getTotalGross($payslips);
        $total_addition = $this->getTotalAddition($payslips);
        $total_deduction = $this->getTotalDeduction($payslips);
        $total_tax = $this->getTaxTotal($payslips);
        return [
            'id' =>   $payslip_summary->id,
            'month' => Carbon::parse($cycle_start_date)->format('F Y'),
            'status' => $status,
            'disburse_date' => $disbursed_at ? Carbon::parse($disbursed_at)->format('j F') : 'N/A',
            'cycle' => Carbon::parse($cycle_start_date)->format('M d').' - '.Carbon::parse($payslip_summary->cycle_end_date)->format('M d'),
            'total_gross' => floatValFormat($total_gross),
            'addition_total' => floatValFormat($total_addition),
            'deduction_total' => floatValFormat($total_deduction),
            'tax_total' => floatValFormat($total_tax),
            'net_pay' => floatValFormat(($total_gross + $total_addition) - ($total_deduction + $total_tax)),
            'disbursed_at_raw' => $disbursed_at,
            'month_raw' => $cycle_start_date,
        ];
    }

    private function getTotalGross($payslips)
    {
        $gross_salary = 0;
        foreach ($payslips as $payslip)
        {
            $gross_salary += json_decode($payslip->salary_breakdown, 1)['gross_salary_breakdown']['gross_salary'];
        }
        return $gross_salary;
    }

    private function getTotalAddition($payslips)
    {
        $total_addition = 0;
        foreach ($payslips as $payslip)
        {
            $additions = json_decode($payslip->salary_breakdown, 1)['payroll_component']['addition'];
            foreach($additions as $addition)
            {
                $total_addition += $addition;
            }
        }
        return $total_addition;
    }

    private function getTotalDeduction($payslips)
    {
        $total_deduction = 0;
        foreach ($payslips as $payslip)
        {
            $deductions = json_decode($payslip->salary_breakdown, 1)['payroll_component']['deduction'];
            foreach($deductions as $key => $deduction)
            {
                if ($key == 'tax') continue;
                $total_deduction += $deduction;
            }
        }
        return $total_deduction;
    }

    private function getTaxTotal($payslips)
    {
        $total_tax = 0;
        foreach ($payslips as $payslip)
        {
            $deductions = json_decode($payslip->salary_breakdown, 1)['payroll_component']['deduction'];
            foreach($deductions as $key => $deduction)
            {
                if ($key == 'tax') $total_tax += $deduction;
            }
        }
        return $total_tax;
    }
}
