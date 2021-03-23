<?php namespace Sheba\Business\PayrollSetting;

use Sheba\Business\PayrollComponent\Creator as PayrollComponentCreator;
use Sheba\Dal\PayrollSetting\PayrollSettingRepository;

class Creator
{
    private $payrollSettingRequest;
    private $payrollSettingRepository;
    private $payrollComponentCreator;

    public function __construct(PayrollSettingRepository $payroll_setting_repository, PayrollComponentCreator $payroll_setting_creator)
    {
        $this->payrollSettingRepository = $payroll_setting_repository;
        $this->payrollComponentCreator = $payroll_setting_creator;
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
        $this->payrollComponentCreator->setPayrollSetting($payroll_setting)->create();
    }

    private function payrollSettingData()
    {
        return [
            'business_id' => $this->payrollSettingRequest->getBusiness()->id,
            'payment_schedule' => $this->payrollSettingRequest->getPaymentSchedule(),
        ];
    }
}