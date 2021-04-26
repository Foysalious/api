<?php namespace Sheba\Business\Payslip;

use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdownCalculate;
use App\Sheba\Business\PayrollComponent\Components\PayrollComponentCalculation;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Dal\Payslip\PayslipRepository;

class Updater
{
    private $grossSalaryBreakdownCalculate;
    private $payrollComponentCalculation;
    private $businessMemberRepository;
    private $payslipRepository;
    private $businessMember;
    private $scheduleDate;
    private $grossSalary;
    private $business;
    private $payslip;
    private $addition;
    private $deduction;

    /**
     * Updater constructor.
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param PayslipRepository $payslip_repository
     * @param GrossSalaryBreakdownCalculate $gross_salary_breakdown_calculate
     * @param PayrollComponentCalculation $payroll_component_calculation
     */
    public function __construct(BusinessMemberRepositoryInterface $business_member_repository,
                                PayslipRepository $payslip_repository,
                                GrossSalaryBreakdownCalculate $gross_salary_breakdown_calculate,
                                PayrollComponentCalculation $payroll_component_calculation)
    {
        $this->grossSalaryBreakdownCalculate = $gross_salary_breakdown_calculate;
        $this->businessMemberRepository = $business_member_repository;
        $this->payslipRepository = $payslip_repository;
        $this->payrollComponentCalculation = $payroll_component_calculation;
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
        $this->payrollComponentCalculation->setPayrollSetting($payroll_setting);
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

    public function setAddition($addition)
    {
        $this->addition = $addition;
        return $this;
    }

    public function setDeduction($deduction)
    {
        $this->deduction = $deduction;
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
        $payroll_component_calculation = $this->payrollComponentCalculation->setAddition($this->addition)->setDeduction($this->deduction)->getCalculationBreakdown();
        return [
            'salary_breakdown' => json_encode(array_merge($this->grossSalaryBreakdownCalculate->totalAmountPerComponentFormatted(), $payroll_component_calculation))
        ];
    }
}
