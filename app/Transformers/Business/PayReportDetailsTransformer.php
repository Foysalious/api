<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Payslip\Payslip;

class PayReportDetailsTransformer extends TransformerAbstract
{
    private $businessMember;
    private $role;
    private $department;

    public function __construct(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->role = $this->businessMember->role;
        $this->department = $this->role ? $this->role->businessDepartment : null;
    }

    public function transform(Payslip $payslip)
    {
        return [
            'employee_info' => $this->employeeInfo(),
            'salary_info' => $this->salaryInfo($payslip)
        ];
    }

    private function employeeInfo()
    {
        return [
            'business_member_id' => $this->businessMember->id,
            'employee_id' => $this->businessMember->employee_id,
            'name' => $this->businessMember->profile()->name,
            'pro_pic' => $this->businessMember->profile()->pro_pic,
            'email' => $this->businessMember->profile()->email,
            'mobile' => $this->businessMember->profile()->mobile,
            'join_date' => $this->businessMember->join_date->format('F Y'),
            'designation' => $this->role ? $this->role->name : null,
            'department' => $this->department ? $this->department->name : null,
        ];
    }

    private function salaryInfo($payslip)
    {
        $salary_break_down = $payslip->salaryBreakdown()['gross_salary_breakdown'];
        return [
            'schedule_date' => $payslip->schedule_date->format('Y-m-d'),
            'basic_salary' => $salary_break_down['basic_salary'],
            'house_rent' => $salary_break_down['house_rent'],
            'medical_allowance' => $salary_break_down['medical_allowance'],
            'conveyance' => $salary_break_down['conveyance'],
            'gross_salary' => $salary_break_down['gross_salary'],
            'net_payable' => $salary_break_down['gross_salary'],
        ];
    }
}