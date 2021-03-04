<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Payslip\Payslip;

class PayRunListTransformer extends TransformerAbstract
{
    public function transform(Payslip $payslip)
    {
        $gross_salary = $this->getGrossSalary($payslip->businessMember);
        $business_member = $payslip->businessMember;
        $department = $business_member->department();
        return [
            'id' =>  $payslip->id,
            'business_member_id' => $payslip->business_member_id,
            'employee_id' => $business_member->employee_id ? $business_member->employee_id : 'N/A',
            'employee_name' => $business_member->profile()->name,
            'department' => $department ? $department->name : 'N/A',
            'schedule_date' => Carbon::parse($payslip->schedule_date)->format('Y-m-d'),
            'gross_salary' => floatValFormat($gross_salary),
            'net_payable' => floatValFormat($gross_salary)
        ];
    }

    private function getGrossSalary(BusinessMember $business_member)
    {
        return $business_member->salary ? $business_member->salary->gross_salary : 0;
    }
}
