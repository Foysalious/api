<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PayReportSummaryListTransformer extends TransformerAbstract
{
    public function transform($payslip_summary)
    {
        $payslips = $payslip_summary->payslip;
        return [
                'id' =>   $payslip_summary->id,
                'status' => $payslip_summary->status,
                'disburse_date' => Carbon::parse($payslip_summary->disbursed_at)->format('j F'),
                'cycle' => Carbon::parse($payslip_summary->cycle_start_date)->format('M d').' - '.Carbon::parse($payslip_summary->cycle_end_date)->format('M d'),
                'total_gross' => $this->getTotalGross($payslips),
                'addition_total' => $this->getTotalAddition($payslips),
                'deduction_total' => $this->getTotalDeduction($payslips),
                'tax_total' => $this->getTaxTotal($payslips),
                'disbursed_at_raw' => $payslip_summary->disbursed_at
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
