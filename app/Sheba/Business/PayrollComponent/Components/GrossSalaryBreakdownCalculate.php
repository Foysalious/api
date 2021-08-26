<?php namespace App\Sheba\Business\PayrollComponent\Components;

use App\Models\BusinessMember;
use App\Sheba\Business\Attendance\AttendanceBasicInfo;
use App\Sheba\Business\PayrollSetting\PayrollCommonCalculation;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\TargetType;
use Sheba\Dal\PayrollComponent\Type;

class GrossSalaryBreakdownCalculate
{
    use PayrollCommonCalculation, AttendanceBasicInfo;
    private $componentPercentage;
    private $totalAmountPerComponent;
    private $grossSalaryBreakdownWithTotalAmount;
    private $breakdownData = [];
    private $joiningDate = null;
    private $timeFrame;
    private $business;

    public function __construct()
    {
        $this->componentPercentage = new GrossSalaryComponent();
        $this->totalAmountPerComponent = new GrossSalaryComponent();
        $this->grossSalaryBreakdownWithTotalAmount = [];
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setJoiningDate($joining_date)
    {
        $this->joiningDate = $joining_date;
        return $this;
    }

    public function setTimeFrame($time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    /**
     * @param $payroll_setting
     * @param $business_member
     * @return mixed
     */
    public function componentPercentageBreakdown($payroll_setting, $business_member)
    {
        $gross_components = $this->getBusinessMemberGrossComponent($payroll_setting, $business_member);
        $salary = $business_member->salary ? $business_member->salary->gross_salary : 0;
        $data = [];
        $total_percentage = 0;
        foreach ($gross_components as $payroll_component) {
            $percentage = floatValFormat(json_decode($payroll_component->setting, 1)['percentage']);
            $total_percentage += $percentage;
            array_push($data, [
                'id' => $payroll_component->id,
                'payroll_setting_id' => $payroll_component->payroll_setting_id,
                'name' => $payroll_component->name,
                'title' => $payroll_component->is_default ? Components::getComponents($payroll_component->name)['value'] : $payroll_component->value, // If it is Default Component Title will come from Class otherwise from DB
                'percentage' => $percentage,
                'amount' => $this->percentageToAmountCalculation($salary, $percentage),
                'type' => $payroll_component->type,
                'is_default' => $payroll_component->is_default,
                'is_active' => $payroll_component->is_default ? 1 : $payroll_component->is_active,
                'is_taxable' => $payroll_component->is_taxable,
                'is_overwritten' => $payroll_component->target_id == $business_member->id ? 1 : 0
            ]);
        }

        $final_data['breakdown'] = $data;
        $final_data['total_percentage'] = $total_percentage;

        return $final_data;
    }

    /**
     * @param BusinessMember $business_member
     * @return array
     */
    public function payslipComponentPercentageBreakdown(BusinessMember $business_member)
    {
        $payroll_setting = $business_member->business->payrollSetting;
        $gross_components = $this->getBusinessMemberGrossComponent($payroll_setting, $business_member);
        $data = [];
        $breakdown_data = [];
        foreach ($gross_components as $payroll_component) {
            $percentage = floatValFormat(json_decode($payroll_component->setting, 1)['percentage']);
            $data[$payroll_component->name] = $percentage;
            array_push($breakdown_data, [
                'name' => $payroll_component->name,
                'is_default' => $payroll_component->is_default,
                'is_taxable' => $payroll_component->is_taxable,
                'percentage' => $percentage
            ]);
            $this->breakdownData = $breakdown_data;
        }
        return $data;
    }

    public function getGrossBreakdown()
    {
        return $this->breakdownData;
    }


    public function totalAmountPerComponent($gross_salary, $gross_salary_breakdown_percentage)
    {
        $total_prorated_gross_salary = $gross_salary;
        if($this->joiningDate){
            $period = $this->createPeriodByTime($this->timeFrame->start, $this->timeFrame->end);
            $total_working_days = $this->getTotalBusinessWorkingDays($period, $this->business->officeHour);
            $total_days_after_joining = $this->getTotalBusinessWorkingDays($this->createPeriodByTime($this->joiningDate, $this->timeFrame->end), $this->business->officeHour);
            $total_prorated_gross_salary = floatValFormat($this->oneWorkingDayAmount($gross_salary, $total_working_days) * $total_days_after_joining);
        }
        $gross_salary = $total_prorated_gross_salary;
        $data = ['gross_salary' => $gross_salary];
        foreach ($gross_salary_breakdown_percentage as $breakdown_name => $breakdown_value) {
            $data[$breakdown_name] = floatValFormat(($gross_salary * $breakdown_value) / 100);
        }
        return $data;
    }

    public function filterGrossComponentForSpecificBusinessMember($payroll_components, $payroll_component_by_target)
    {
        foreach ($payroll_component_by_target as $target){
            $payroll_components->search(function($payroll_components_value, $payroll_components_value_index) use($target, $payroll_components){
                if($payroll_components_value->name == $target->name) return $payroll_components->forget($payroll_components_value_index);
            });
        } // It will filter employee target wise components with global components and remove from global if specific business member wise target exists

        return $payroll_components->merge($payroll_component_by_target); // Merging both collection will make a collection which is only for specific business member
    }

    private function percentageToAmountCalculation($gross_salary, $percentage)
    {
        return floatValFormat(($gross_salary * $percentage) / 100);
    }

    private function getBusinessMemberGrossComponent($payroll_setting, $business_member)
    {
        $payroll_components = $payroll_setting->components()->where('type', Type::GROSS)->where(function($query) {
            return $query->where('target_type', null)->orWhere('target_type', TargetType::GENERAL);
        })->where(function($query) {
            return $query->where('is_default', 1)->orWhere('is_active',1);
        })->orderBy('type')->get();
        $gross_components = $payroll_components;
        $payroll_component_by_target = $payroll_setting->components()->where('type', Type::GROSS)->where('target_id', $business_member->id)->where('is_active', 1)->orderBy('name')->get();
        if ($payroll_component_by_target) $gross_components = $this->filterGrossComponentForSpecificBusinessMember($payroll_components, $payroll_component_by_target);

        return $gross_components;
    }
}
