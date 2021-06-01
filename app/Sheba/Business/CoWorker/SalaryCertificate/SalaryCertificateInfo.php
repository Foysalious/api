<?php namespace Sheba\Business\CoWorker\SalaryCertificate;

use App\Models\Business;
use App\Models\Member;
use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdownCalculate;
use Carbon\Carbon;
use NumberFormatter;

class SalaryCertificateInfo
{
    private $business;
    private $member;
    private $businessMember;

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
       $this->business = $business;
       return $this;
    }

    /**
     * @param Member $member
     * @return $this
     */
    public function setMember(Member $member)
    {
       $this->member = $member;
       return $this;
    }

    /**
     * @param $business_member
     * @return $this
     */
    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function get()
    {
        return [
            'created_date' => Carbon::parse(Carbon::now())->format('F d, Y'),
            'business_name' => $this->business->name,
            'business_logo' => $this->business->logo,
            'employee_info' => $this->getEmployeeInfo($this->member, $this->businessMember),
            'salary_info' => $this->getSalaryInfo($this->businessMember),
        ];
    }

    private function getEmployeeInfo($member, $business_member)
    {
        $profile = $member->profile;
        $role = $business_member ? $business_member->role : null;
        $department = $role ? $role->businessDepartment : null;
        $department_name = $department ? $department->name : null;
        $designation = $role ? $role->name : null;

        return [
            'name' => $profile->name,
            'designation' => $designation,
            'department' => $department_name,
            'joining_date' => Carbon::parse($business_member->join_date)->format('jS F Y')
        ];
    }

    private function getSalaryInfo($business_member)
    {
        $payroll_setting = $this->business->payrollSetting;
        $payroll_percentage_breakdown = (new GrossSalaryBreakdownCalculate())->componentPercentageBreakdown($payroll_setting, $business_member);
        $salary = $business_member->salary;

        return [
            'salary_breakdown' => $payroll_percentage_breakdown['breakdown'],
            'gross_salary' => $salary ? floatValFormat($salary->gross_salary) : null,
            'gross_salary_in_word' => $salary ? $this->getAmountInWord(floatValFormat($salary->gross_salary)) : null
        ];

    }

    private function getAmountInWord($amount)
    {
        return ucwords(str_replace('-', ' ', (new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($amount)));
    }

}