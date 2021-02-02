<?php namespace Sheba\Business\PayrollSetting;

use Sheba\Dal\PayrollSetting\PayrollSettingRepository;

class Updater
{
    private $payrollSettingRequest;
    private $payrollSettingRepository;

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
    }
}