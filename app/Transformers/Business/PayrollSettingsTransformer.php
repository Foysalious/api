<?php namespace App\Transformers\Business;

use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdown;
use Sheba\Business\PayrollComponent\Components\MedicalAllowance;
use Sheba\Business\PayrollComponent\Components\BasicSalary;
use Sheba\Business\PayrollComponent\Components\Conveyance;
use Sheba\Business\PayrollComponent\Components\HouseRent;
use Sheba\Dal\PayrollComponent\PayrollComponent;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use Sheba\Dal\PayrollComponent\Components;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\PayrollComponent\Type;

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
        $this->payrollComponentData = (new GrossSalaryBreakdown())->salaryBreakdown($payroll_setting);
        $count = 0;
        if ($this->payrollComponentData['basic_salary'] > 0) $count++;
        if ($this->payrollComponentData['house_rent'] > 0) $count++;
        if ($this->payrollComponentData['medical_allowance'] > 0) $count++;
        if ($this->payrollComponentData['conveyance'] > 0) $count++;
        $salary_breakdown_completion = round((($count / 4) * 50), 0);
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
        if ($payroll_setting->payment_schedule) $count++;
        if ($payroll_setting->pay_day) $count++;
        if ($payroll_setting->is_enable) $count++;
        $pay_schedule_completion = round((($count / 3) * 50), 0);

        $this->payScheduleData = [
            'is_enable' => $payroll_setting->is_enable,
            'payment_schedule' => $payroll_setting->payment_schedule,
            'pay_day' => $payroll_setting->pay_day,
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
}