<?php namespace Sheba\Business\PayrollSetting;

use Sheba\Dal\PayrollSetting\PayrollSettingRepository;

class Creator
{
    private $payrollSettingRequest;
    private $payrollSettingRepository;

    public function __construct(PayrollSettingRepository $payroll_setting_repository)
    {
        $this->payrollSettingRepository = $payroll_setting_repository;
    }

    /**
     * @param Requester $payroll_setting_request
     * @return Creator
     */
    public function setPayrollSettingRequest(Requester $payroll_setting_request)
    {
        $this->payrollSettingRequest = $payroll_setting_request;
        return $this;
    }

    public function create()
    {
        $payroll_setting = $this->payrollSettingRepository->create($this->payrollSettingData());

    }

    private function payrollSettingData()
    {
        return [
            'business_id' => $this->payrollSettingRequest->getBusiness()
        ];
    }
}