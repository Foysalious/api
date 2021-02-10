<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Payslip\Payslip;

class PayRunListTransformer extends TransformerAbstract
{
    public function transform(Payslip $payslip)
    {
        $gross_salary = $this->getGrossSalary($payslip->businessMember);
        $business_member = $payslip->businessMember;
        $member = $business_member->member;
        $department = $business_member->department();
        return [
            'id' =>  $payslip->id,
            'employee_id' => $business_member->employee_id ? $business_member->employee_id : 'N/A',
            'employee_name' => $member->profile->name ? $member->profile->name : Null,
            'business_member_id' => $payslip->business_member_id,
            'department' => $department ? $department->name : 'N/A',
            'gross_salary' => floatval($gross_salary),
            'net_payable' => floatval($gross_salary)
        ];
    }

    private function getGrossSalary(BusinessMember $business_member)
    {
        return $business_member->salary ? $business_member->salary->gross_salary : 0;
    }
}
