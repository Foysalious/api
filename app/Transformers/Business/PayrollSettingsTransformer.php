<?php namespace App\Transformers\Business;

use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdownCalculate;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use Sheba\Dal\PayrollSetting\PayDayType;
use League\Fractal\TransformerAbstract;

class PayrollSettingsTransformer extends TransformerAbstract
{
    private $payrollComponentData = [];
    private $payScheduleData;
    private $totalGrossPercentage = 0;

    public function __construct()
    {
        $this->payScheduleData = [];
    }

    /**
     * @param PayrollSetting $payroll_setting
     * @return array
     */
    public function transform(PayrollSetting $payroll_setting)
    {
        return [
            'id' => $payroll_setting->id,
            'business_id' => $payroll_setting->business_id,
            'salary_breakdown' => array_values($this->grossSalaryBreakdown($payroll_setting)),
            'gross_salary_total' => $this->totalGrossPercentage,
            'salary_breakdown_completion' => $this->totalGrossPercentage,
            'pay_components' => $this->payComponents($payroll_setting),
            'pay_schedule' => $this->paySchedule($payroll_setting),
            'payroll_setting_completion' => $this->payrollSettingCompletion(),
        ];
    }

    /*
    private function grossSalaryBreakdown_($payroll_setting)
    {
        $payroll_percentage_breakdown = (new GrossSalaryBreakdownCalculate())->componentPercentageBreakdown($payroll_setting);
        $count = 0;
        if (($payroll_percentage_breakdown->basicSalary > 0) || ($payroll_percentage_breakdown->houseRent > 0) || ($payroll_percentage_breakdown->medicalAllowance > 0) || ($payroll_percentage_breakdown->conveyance > 0)) $count++;
        $salary_breakdown_completion = round((($count / 1) * 50), 0);

        $this->payrollComponentData[Components::BASIC_SALARY] = $payroll_percentage_breakdown->basicSalary;
        $this->payrollComponentData[Components::HOUSE_RENT] = $payroll_percentage_breakdown->houseRent;
        $this->payrollComponentData[Components::MEDICAL_ALLOWANCE] = $payroll_percentage_breakdown->medicalAllowance;
        $this->payrollComponentData[Components::CONVEYANCE] = $payroll_percentage_breakdown->conveyance;
        $this->payrollComponentData['salary_breakdown_completion'] = $salary_breakdown_completion;
        return $this->payrollComponentData;
    }*/

    private function grossSalaryBreakdown($payroll_setting)
    {
        $payroll_components = $payroll_setting->components()->where('type', Type::GROSS)->get();
        foreach ($payroll_components as $payroll_component) {
            $salary_percentage = json_decode($payroll_component->setting, 1);
            $percentage_value = $salary_percentage['percentage'];
            if ($payroll_component->is_active) $this->totalGrossPercentage += $percentage_value;
            array_push($this->payrollComponentData, [
                'id' => $payroll_component->id,
                'key' => $payroll_component->name,
                'title' => $payroll_component->is_default ? Components::getComponents($payroll_component->name)['value'] : $payroll_component->value,
                'value' => (int)$salary_percentage['percentage'],
                'is_default' => $payroll_component->is_default,
                'is_active' => $payroll_component->is_active,
                'taxable' => $payroll_component->is_taxable,
            ]);
        }
        return $this->payrollComponentData;
    }

    /**
     * @param $payroll_setting
     * @return array
     */
    private function paySchedule($payroll_setting)
    {
        $count = 0;
        if ($payroll_setting->is_enable) $count++;
        $pay_schedule_completion = round((($count / 1) * 50), 0);

        $this->payScheduleData = [
            'is_enable' => $payroll_setting->is_enable,
            'payment_schedule' => $payroll_setting->payment_schedule,
            'pay_day' => [
                'type' => $payroll_setting->pay_day_type,
                'date' => $payroll_setting->pay_day_type == PayDayType::FIXED_DATE ? $payroll_setting->pay_day : null,
            ],
            'pay_schedule_completion' => $pay_schedule_completion
        ];
        return $this->payScheduleData;
    }

    /**
     * @return float
     */
    private function payrollSettingCompletion()
    {
        $total = ( $this->totalGrossPercentage / 2 ) + $this->payScheduleData['pay_schedule_completion'];
        return round($total, 0);
    }

    private function payComponents($payroll_setting)
    {
        $addition_components = $payroll_setting->components->where('type',Type::ADDITION)->sortBy('name');
        $deduction_components = $payroll_setting->components->where('type',Type::DEDUCTION)->sortBy('name');
        $addition = $this->getAdditionComponents($addition_components);
        $deduction = $this->getDeductionComponents($deduction_components);

        return array_merge($addition, $deduction);
    }

    private function getAdditionComponents($addition_components)
    {
        $data = [];
        foreach ($addition_components as $addition) {
            if (!$addition->is_default) {
                $packages = $this->makeComponentsData($addition);
                $data['addition'][] = ['id' => $addition->id, 'name' => $addition->value, 'is_default' => 0, 'package' => $packages];
            }
            if ($addition->is_default) $data['addition'][] = ['id' => $addition->id, 'name' => Components::getComponents($addition->name)['value'], 'is_default' => 1];
        }
        return $data;
    }

    private function getDeductionComponents($deduction_components)
    {
        $data = [];
        foreach ($deduction_components as $deduction) {
            if (!$deduction->is_default) {
                $packages = $this->makeComponentsData($deduction);
                $data['addition'][] = ['id' => $deduction->id, 'name' => $deduction->value, 'is_default' => 0, 'package' => $packages];
            }
            if ($deduction->is_default) $data['deduction'][] = ['id' => $deduction->id, 'name' => Components::getComponents($deduction->name)['value'], 'is_default' => 1];
        }
        return $data;
    }

    private function makeComponentsData($component)
    {
        $component_packages = $component->componentPackages;
        $data = [];
        foreach ( $component_packages as $packages) {
            $targets = $packages->packageTargets;
            array_push($data , [
                'package_key' => $packages->key,
                'package_name' => $packages->name,
                'is_active' => $packages->is_active,
                'is_taxable' => $packages->is_taxable,
                'calculation_type' => $packages->calculation_type,
                'is_percentage' => $packages->is_percentage,
                'on_what' => $packages->on_what,
                'amount' => $packages->amount,
                'schedule_type' => $packages->schedule_type,
                'periodic_schedule' => $packages->periodic_schedule,
                'schedule_date' => $packages->schedule_date,
                'target' => $this->getTarget($targets)
            ]);
        }
        return $data;
    }

    private function getTarget($targets)
    {
        $data = [];
        foreach ($targets as $target){
            array_push($data, [
                'effective_for' => $target->effective_for,
                'target_id' => $target->target_id
            ]);
        }
        return $data;
    }
}
