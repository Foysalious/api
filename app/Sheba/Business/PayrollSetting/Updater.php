<?php namespace Sheba\Business\PayrollSetting;

use Sheba\Dal\PayrollSetting\PayrollSettingRepository;
use Sheba\Dal\PayrollSetting\PayrollSetting;

class Updater
{
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
        return [
            'is_enable' => $this->payrollSettingRequest->getIsEnable(),
            'pay_day' => $this->payrollSettingRequest->getPayDay()
        ];
    }
}