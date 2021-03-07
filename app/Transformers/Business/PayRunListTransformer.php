<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\Payslip\Payslip;

class PayRunListTransformer extends TransformerAbstract
{
    private $grossSalary;

    public function transform(Payslip $payslip)
    {
        $this->grossSalary = $this->getGrossSalary($payslip->businessMember);
        $business_member = $payslip->businessMember;
        $department = $business_member->department();
        return [
            'id' =>  $payslip->id,
            'business_member_id' => $payslip->business_member_id,
            'employee_id' => $business_member->employee_id ? $business_member->employee_id : 'N/A',
            'employee_name' => $business_member->profile()->name,
            'department' => $department ? $department->name : 'N/A',
            'schedule_date' => Carbon::parse($payslip->schedule_date)->format('Y-m-d'),
            'gross_salary' => floatValFormat($this->grossSalary),
            'addition' => $this->getTotal($payslip,'addition'),
            'deduction' => $this->getTotal($payslip,'deduction'),
            'net_payable' => $this->getTotal($payslip,'net_payable'),
            'gross_salary_breakdown' => $this->getGrossBreakdown($payslip),
            'addition_breakdown' => $this->getComponentBreakdown($payslip,'addition'),
            'deduction_breakdown' => $this->getComponentBreakdown($payslip,'deduction'),
        ];
    }

    private function getGrossSalary(BusinessMember $business_member)
    {
        return $business_member->salary ? $business_member->salary->gross_salary : 0;
    }

    private function getTotal($payslip, $type)
    {
        $salary_breakdown = json_decode($payslip->salary_breakdown, 1);
        $addition = 0;
        $deduction = 0;
        foreach ($salary_breakdown['payroll_component'] as $key => $payroll_component) {
            if ($key == 'addition') {
                foreach ($payroll_component as $component) {
                    $addition += $component;
                }
            }

            if ($key == 'deduction') {
                foreach ($payroll_component as $component) {
                    $deduction += $component;
                }
            }
        }
        $net_payable = floatValFormat(($this->grossSalary + $addition) - $deduction);
        if ($type == 'net_payable') return $net_payable;
        if ($type == 'addition') return $addition;
        if ($type == 'deduction') return $deduction;
    }

    private function getGrossBreakdown($payslip)
    {
        $salary_breakdown = json_decode($payslip->salary_breakdown, 1)['gross_salary_breakdown'];

        $data  = [];
        foreach ($salary_breakdown as $key => $payroll_component) {
            if ($key == 'gross_salary') continue;
            $data[$key] = $payroll_component;
        }
        return $data;
    }

    private function getComponentBreakdown($payslip, $type)
    {
        $salary_breakdown = json_decode($payslip->salary_breakdown, 1)['payroll_component'];

        $data  = [];
        foreach ($salary_breakdown as $key => $payroll_component) {
            if ($key == $type) {
                foreach ($payroll_component as $item => $component) {
                    $data[$item] = $component;
                }
            }
        }
        return $data;
    }
}
