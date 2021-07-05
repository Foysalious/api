<?php namespace Sheba\Business\CoWorker\SalaryCertificate;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Business\BusinessBasicInformation;
use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdownCalculate;
use Carbon\Carbon;
use NumberFormatter;

class SalaryCertificateInfo
{
    use BusinessBasicInformation;

    /** @var Business $business */
    private $business;
    /** @var Member $member */
    private $member;
    /** @var Profile $profile */
    private $profile;
    /** @var BusinessMember $businessMember */
    private $businessMember;
    private $profile;

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->business = $business_member->business;
        $this->member = $business_member->member;
        $this->profile = $this->member->profile;
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        return [
            'created_date' => Carbon::parse(Carbon::now())->format('F d, Y'),
            'business_name' => $this->business->name,
            'business_logo' => $this->isDefaultImageByUrl($this->business->logo) ? null : $this->business->logo,
            'employee_info' => $this->getEmployeeInfo(),
            'salary_info' => $this->getSalaryInfo(),
        ];
    }

    private function getEmployeeInfo()
    {
        $role = $this->businessMember ? $this->businessMember->role : null;
        $department = $role ? $role->businessDepartment : null;
        $department_name = $department ? $department->name : null;
        $designation = $role ? $role->name : null;

        return [
            'name' => trim($this->profile->name),
            'designation' => $designation,
            'department' => $department_name,
            'joining_date' => Carbon::parse($this->businessMember->join_date)->format('jS F Y')
        ];
    }

    private function getSalaryInfo()
    {
        $payroll_setting = $this->business->payrollSetting;
        $payroll_percentage_breakdown = (new GrossSalaryBreakdownCalculate())->componentPercentageBreakdown($payroll_setting, $this->businessMember);
        $salary = $this->businessMember->salary;

        return [
            'salary_breakdown' => array_map(function ($salary) {
                return [
                    'title' => $salary['title'],
                    'amount' => $this->parseSalary($salary['amount'])
                ];
            }, $payroll_percentage_breakdown['breakdown']),
            'gross_salary' => $salary ? $this->parseSalary($salary->gross_salary) : null,
            'gross_salary_in_word' => $salary ? $this->getAmountInWord(floatValFormat($salary->gross_salary)) : null
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

    /**
     * @param $value
     * @return string
     */
    private function parseSalary($value)
    {
        return number_format($value, 2, ".", ",");
    }

}