<?php namespace App\Transformers\Business;

use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdownCalculate;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use Sheba\Dal\PayrollSetting\PayDayType;
use League\Fractal\TransformerAbstract;

class PayrollSettingsTransformer extends TransformerAbstract
{
    private $payrollComponentData;
    private $payScheduleData;

    public function __construct()
    {
        $this->payrollComponentData = [];
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
            'salary_breakdown' => $this->grossSalaryBreakdown($payroll_setting),
            'pay_components' => $this->payComponents($payroll_setting),
            'pay_schedule' => $this->paySchedule($payroll_setting),
            'payroll_setting_completion' => $this->payrollSettingCompletion(),
        ];
    }

    /**
     * @param $payroll_setting
     * @return array
     */
    private function grossSalaryBreakdown($payroll_setting)
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
        $total = $this->payrollComponentData['salary_breakdown_completion'] + $this->payScheduleData['pay_schedule_completion'];
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
            if (!$addition->is_default) $data['addition'][] = ['id' => $addition->id, 'name' => ucwords(implode(" ", explode("_",$addition->name))), 'is_default' => 0];
            if ($addition->is_default) $data['addition'][] = ['id' => $addition->id, 'name' => Components::getComponents($addition->name)['value'], 'is_default' => 1];
        }
        return $data;
    }

    private function getDeductionComponents($deduction_components)
    {
        $data = [];
        foreach ($deduction_components as $deduction) {
            if (!$deduction->is_default) $data['deduction'][] = ['id' => $deduction->id, 'name' => ucwords(implode(" ", explode("_",$deduction->name))), 'is_default' => 0];
            if ($deduction->is_default) $data['deduction'][] = ['id' => $deduction->id, 'name' => Components::getComponents($deduction->name)['value'], 'is_default' => 1];
        }
        return $data;
    }
}
