<?php namespace App\Transformers\Business;

use App\Sheba\Business\ComponentPackage\Formatter;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\TargetType;
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

    private function grossSalaryBreakdown($payroll_setting)
    {
        $payroll_components = $payroll_setting->components->where('type', Type::GROSS)->where('target_type', TargetType::GENERAL);
        foreach ($payroll_components as $payroll_component) {
            $salary_percentage = json_decode($payroll_component->setting, 1);
            $percentage_value = $salary_percentage['percentage'];
            if ($payroll_component->is_active) $this->totalGrossPercentage += $percentage_value;
            array_push($this->payrollComponentData, [
                'id' => $payroll_component->id,
                'key' => $payroll_component->name,
                'title' => $payroll_component->is_default ? Components::getComponents($payroll_component->name)['value'] : $payroll_component->value,
                'value' => floatValFormat($salary_percentage['percentage']),
                'is_default' => $payroll_component->is_default,
                'is_active' => $payroll_component->is_default ? 1 : $payroll_component->is_active,
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
        $total = ($this->totalGrossPercentage/2 + $this->payScheduleData['pay_schedule_completion']);
        return round($total, 0);
    }

    private function payComponents($payroll_setting)
    {
        $addition_components = $payroll_setting->components->where('type', Type::ADDITION)->sortBy('name');
        $deduction_components = $payroll_setting->components->where('type', Type::DEDUCTION)->sortBy('name');
        $addition = $this->getAdditionComponents($addition_components);
        $deduction = $this->getDeductionComponents($deduction_components);

        return array_merge($addition, $deduction);
    }

    private function getAdditionComponents($addition_components)
    {
        $data = [];
        foreach ($addition_components as $addition) {
            if (!$addition->is_default) {
                $package_formatter = new Formatter();
                $packages = $package_formatter->makePackageData($addition);
                $data['addition'][] = ['id' => $addition->id, 'key' =>$addition->name,  'value' => $addition->value, 'is_default' => 0, 'is_taxable' => $addition->is_taxable, 'package' => $packages];
            }
            if ($addition->is_default) $data['addition'][] = ['id' => $addition->id, 'key' =>$addition->name, 'value' => Components::getComponents($addition->name)['value'], 'is_default' => 1,  'is_taxable' => $addition->is_taxable];
        }
        return $data;
    }

    private function getDeductionComponents($deduction_components)
    {
        $data = [];
        foreach ($deduction_components as $deduction) {
            if (!$deduction->is_default) {
                $package_formatter = new Formatter();
                $packages = $package_formatter->makePackageData($deduction);
                $data['deduction'][] = ['id' => $deduction->id, 'key' =>$deduction->name,  'value' => $deduction->value, 'is_default' => 0, 'package' => $packages];
            }
            if ($deduction->is_default) $data['deduction'][] = ['id' => $deduction->id, 'key' =>$deduction->name, 'value' => Components::getComponents($deduction->name)['value'], 'is_default' => 1];
        }
        return $data;
    }
}
