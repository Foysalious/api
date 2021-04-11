<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\Type;
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
        $payroll_components = $payslip->salaryBreakdown()['payroll_component'];
        return [
            'employee_info' => $this->employeeInfo(),
            'salary_info' => $this->salaryInfo($payslip),
            'addition' => $this->getPayrollComponentBreakdown($payroll_components, Type::ADDITION),
            'deduction' => $this->getPayrollComponentBreakdown($payroll_components, Type::DEDUCTION)
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
     * @param $payroll_components
     * @param $type
     * @return array
     */
    private function getPayrollComponentBreakdown($payroll_components, $type)
    {
        $total = 0;
        $final_data = [];
        foreach ($payroll_components as $component_type => $component_breakdown) {
            if ($component_type == $type) {
                foreach ($component_breakdown as $component => $component_value) {
                    $total += $component_value;
                    $final_data['breakdown'][ucwords(implode(" ", explode("_", $component)))] = $component_value;
                }
            }
        }
        $final_data['total'] = $total;
        return $final_data;
    }
}
