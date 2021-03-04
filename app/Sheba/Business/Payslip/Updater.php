<?php namespace Sheba\Business\Payslip;

use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdownCalculate;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Dal\Payslip\PayslipRepository;

class Updater
{
    private $grossSalaryBreakdownCalculate;
    private $businessMemberRepository;
    private $payslipRepository;
    private $businessMember;
    private $scheduleDate;
    private $grossSalary;
    private $business;
    private $payslip;

    /**
     * Updater constructor.
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param PayslipRepository $payslip_repository
     * @param GrossSalaryBreakdownCalculate $gross_salary_breakdown_calculate
     */
    public function __construct(BusinessMemberRepositoryInterface $business_member_repository,
                                PayslipRepository $payslip_repository,
                                GrossSalaryBreakdownCalculate $gross_salary_breakdown_calculate)
    {
        $this->grossSalaryBreakdownCalculate = $gross_salary_breakdown_calculate;
        $this->businessMemberRepository = $business_member_repository;
        $this->payslipRepository = $payslip_repository;
    }

    /**
     * @param $business_member
     * @return $this
     */
    public function setBusinessMember($business_member)
    {
        $this->businessMember = $this->businessMemberRepository->find($business_member);
        $this->business = $this->businessMember->business;
        $payroll_setting = $this->business->payrollSetting;
        $this->grossSalaryBreakdownCalculate->componentPercentageBreakdown($payroll_setting);
        return $this;
    }

    /**
     * @param $gross_salary
     * @return $this
     */
    public function setGrossSalary($gross_salary)
    {
        $this->grossSalary = $gross_salary;
        if (!$this->grossSalary) $this->grossSalary = 0;
        return $this;
    }

    /**
     * @param $schedule_date
     * @return $this
     */
    public function setScheduleDate($schedule_date)
    {
        $this->scheduleDate = $schedule_date;
        return $this;
    }

    public function update()
    {
        $this->payslip = $this->payslipRepository->where('business_member_id', $this->businessMember->id)
            ->where('schedule_date', 'LIKE', '%' . $this->scheduleDate . '%')
            ->first();
        $this->payslipRepository->update($this->payslip, $this->formatPaySlipData());
    }

    /**
     * @return array
     */
    private function formatPaySlipData()
    {
        $this->grossSalaryBreakdownCalculate->totalAmountPerComponent($this->grossSalary);
        return [
            'salary_breakdown' => json_encode($this->grossSalaryBreakdownCalculate->totalAmountPerComponentFormatted())
        ];
    }
}