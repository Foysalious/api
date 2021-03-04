<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\Payslip\Payslip;

class PayRunListTransformer extends TransformerAbstract
{
    private $grossSalary;
    private $netPayable;

    public function transform(Payslip $payslip)
    {
        $this->grossSalary = $this->getGrossSalary($payslip->businessMember);
        $business_member = $payslip->businessMember;
        $department = $business_member->department();
        return [
            'total' => $this->getTotal($payslip),
            'id' =>  $payslip->id,
            'business_member_id' => $payslip->business_member_id,
            'employee_id' => $business_member->employee_id ? $business_member->employee_id : 'N/A',
            'employee_name' => $business_member->profile()->name,
            'department' => $department ? $department->name : 'N/A',
            'schedule_date' => Carbon::parse($payslip->schedule_date)->format('Y-m-d'),
            'gross_salary' => floatValFormat($this->grossSalary),
            'net_payable' => $this->netPayable,
        ];
    }

    private function getGrossSalary(BusinessMember $business_member)
    {
        return $business_member->salary ? $business_member->salary->gross_salary : 0;
    }

    private function getTotal($payslip)
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
        $this->netPayable = floatValFormat(($this->grossSalary + $addition) - $deduction);
        $data = [
            'gross_salary' => floatValFormat ($this->grossSalary),
            'addition' => floatValFormat($addition),
            'deduction' => floatValFormat($deduction),
            'net_payable' => $this->netPayable
        ];

        return $data;
    }
}
