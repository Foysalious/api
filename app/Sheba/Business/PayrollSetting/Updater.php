<?php namespace Sheba\Business\PayrollSetting;

use App\Sheba\Business\PayrollSetting\PayrollCommonCalculation;
use Carbon\Carbon;
use Sheba\Dal\PayrollSetting\PayrollSettingRepository;
use Sheba\Dal\PayrollSetting\PayrollSetting;

class Updater
{
    use PayrollCommonCalculation;

    private $payrollSettingRequest;
    private $payrollSettingRepository;
    private $payrollSetting;

    public function __construct(PayrollSettingRepository $payroll_setting_repository)
    {
        $this->payrollSettingRepository = $payroll_setting_repository;
    }

    /**
     * @param Requester $payroll_setting_request
     */
    public function setPayrollSettingRequest(Requester $payroll_setting_request)
    {
        $this->payrollSettingRequest = $payroll_setting_request;
        return $this;
    }

    public function setPayrollSetting(PayrollSetting $payroll_setting)
    {
        $this->payrollSetting = $payroll_setting;
        return $this;
    }

    public function update()
    {
        $this->payrollSettingRepository->update($this->payrollSetting, $this->payrollSettingData());
    }

    private function payrollSettingData()
    {
        $data =  [
            'is_enable' => $this->payrollSettingRequest->getIsEnable(),
            'pay_day_type' => $this->payrollSettingRequest->getPayDayType(),
            'pay_day' => $this->payrollSettingRequest->getPayDay()
        ];
        if ($this->payrollSetting->next_pay_day == null) $data['next_pay_day'] = $this->nextPayslipGenerationDay($this->payrollSetting->business);
        return $data;
    }
}