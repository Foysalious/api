<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Payslip\Payslip;
use NumberFormatter;

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
        $addition = ['breakdown' => ['overtime' => 300], 'total' => 300];
        $deduction = ['breakdown' => ['tax' => 300], 'total' => 300];
        return [
            'employee_info' => $this->employeeInfo(),
            'salary_info' => $this->salaryInfo($payslip),
            'addition' => $addition,
            'deduction' => $deduction
        ];
    }

    private function employeeInfo()
    {
        $profile = $this->businessMember->profile();
        return [
            'business_member_id' => $this->businessMember->id,
            'company_name' => $this->businessMember->business->name,
            'company_logo' => $this->businessMember->business->logo,
            'employee_id' => $this->businessMember->employee_id,
            'name' => $profile->name,
            'pro_pic' => $profile->pro_pic,
            'email' => $profile->email,
            'mobile' => $profile->mobile,
            'join_date' => Carbon::parse($this->businessMember->join_date)->format('F Y'),
            'designation' => $this->role ? $this->role->name : null,
            'department' => $this->department ? $this->department->name : null,
        ];
    }

    private function salaryInfo($payslip)
    {
        $salary_break_down = $payslip->salaryBreakdown()['gross_salary_breakdown'];
        $salary_month = $payslip->schedule_date;
        return [
            'salary_month' => $salary_month->format('M Y'),
            'schedule_date' => $salary_month->format('Y-m-d'),
            'basic_salary' => $salary_break_down['basic_salary'],
            'house_rent' => $salary_break_down['house_rent'],
            'medical_allowance' => $salary_break_down['medical_allowance'],
            'conveyance' => $salary_break_down['conveyance'],
            'gross_salary' => $salary_break_down['gross_salary'],
            'net_payable' => $salary_break_down['gross_salary'],
            'net_payable_in_word' => $this->getAmountInWord($salary_break_down['gross_salary']),
            //'net_payable_in_word' => 'One Thousand and Five Hundreds Taka Only',
        ];
    }

    /**
     * @param $amount
     * @return string
     */
    private function getAmountInWord($amount)
    {
        return ucwords(str_replace('-', ' ', (new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($amount)));
    }
}
