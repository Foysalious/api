<?php namespace App\Sheba\Business\Payslip;

use App\Sheba\Business\Attendance\AttendanceBasicInfo;
use App\Sheba\Business\PayrollComponent\Components\Deductions\Tax\TaxCalculator;
use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdownCalculate;
use App\Sheba\Business\PayrollComponent\Components\PayrollComponentSchedulerCalculation;
use App\Sheba\Business\PayrollSetting\PayrollCommonCalculation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\BusinessPayslip\BusinessPayslipRepository;
use Sheba\Dal\BusinessWorkingDayHistory\BusinessWorkingDayHistoryRepository;
use Sheba\Dal\PayrollComponentPackage\PayrollComponentPackageRepository;
use Sheba\Dal\PayrollPayDayHistory\PayrollPayDayHistoryRepository;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Payslip\Status;
use Sheba\Dal\TaxHistory\TaxHistoryRepository;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;
use Throwable;

class BusinessWisePayslip
{

    use ModificationFields, PayrollCommonCalculation, AttendanceBasicInfo;

    const AUTO_GENERATED_PAYSLIP = "App\\Console\\Commands\\Payslip";
    const MANUALLY_GENERATED_PAYSLIP = "App\\Console\\Commands\\GeneratePayslip";

    /*** @var GrossSalaryBreakdownCalculate */
    private $grossSalaryBreakdownCalculate;
    /*** @var PayslipRepository */
    private $payslipRepository;
    /*** @var PayrollComponentSchedulerCalculation */
    private $payrollComponentSchedulerCalculation;
    /*** @var PayrollComponentPackageRepository */
    private $payrollComponentPackageRepository;
    /*** @var TaxCalculator */
    private $taxCalculator;
    /*** @var TimeFrame */
    private $timeFrame;
    /*** @var TaxHistoryRepository */
    private $taxHistoryRepository;
    private $period;
    private $businessMembers;
    private $generatedBusinessMemberIds;
    private $className;
    private $generatedFor;
    /*** @var BusinessWorkingDayHistoryRepository $businessWorkingDayRepository */
    private $businessWorkingDayRepository;
    private $business;
    private $payrollSetting;
    /*** @var PayrollPayDayHistoryRepository $payrollPayDayHistoryRepository*/
    private $payrollPayDayHistoryRepository;
    /*** @var BusinessPayslipRepository */
    private $businessPayslipRepo;

    public function __construct()
    {
        $this->grossSalaryBreakdownCalculate = app(GrossSalaryBreakdownCalculate::class);
        $this->payslipRepository = app(PayslipRepository::class);
        $this->payrollComponentSchedulerCalculation = app(PayrollComponentSchedulerCalculation::class);
        $this->payrollComponentPackageRepository = app(PayrollComponentPackageRepository::class);
        $this->taxCalculator = app(TaxCalculator::class);
        $this->timeFrame = app(TimeFrame::class);
        $this->taxHistoryRepository = app(TaxHistoryRepository::class);
        $this->businessPayslipRepo = app(BusinessPayslipRepository::class);
        $this->businessWorkingDayRepository = app(BusinessWorkingDayHistoryRepository::class);
        $this->payrollPayDayHistoryRepository = app(PayrollPayDayHistoryRepository::class);
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setBusinessMember($business_members)
    {
        $this->businessMembers = $business_members;
        return $this;
    }

    public function setPayrollSetting($payroll_setting)
    {
        $this->payrollSetting = $payroll_setting;
        return $this;
    }

    public function setGeneratePeriod($period)
    {
        $this->period = $period;
        return $this;
    }

    public function setGeneratedBusinessMemberIds($generated_business_member_ids)
    {
        $this->generatedBusinessMemberIds = $generated_business_member_ids;
        return $this;
    }

    public function setClass($class_name)
    {
        $this->className = $class_name;
        return $this;
    }

    public function setGeneratedFor($generated_for)
    {
        $this->generatedFor = $generated_for;
        return $this;
    }

    public function calculate()
    {
        $last_pay_day = $this->payrollSetting->last_pay_day;
        $start_date = $this->className === self::MANUALLY_GENERATED_PAYSLIP ? Carbon::parse($this->period) : null;
        $end_date = $this->className === self::MANUALLY_GENERATED_PAYSLIP ? Carbon::parse($this->period) : null;
        $business_office = $this->business->officeHour;
        $start_date = $start_date ? Carbon::parse($start_date)->subMonth()->format('Y-m-d') : ($last_pay_day ? Carbon::parse($last_pay_day)->format('Y-m-d') : Carbon::now()->subMonth()->format('Y-m-d'));
        $end_date = $end_date ? Carbon::parse($end_date)->subDay()->format('Y-m-d') : Carbon::now()->subDay()->format('Y-m-d');
        $total_policy_working_days = $this->getTotalBusinessWorkingDays($this->createPeriodByTime($start_date, $end_date), $business_office);
        $business_working_day_data = [
            'business_id' => $this->business->id,
            'total_working_days' => $total_policy_working_days,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        $business_pay_day_data = [
            'business_id' => $this->business->id,
            'pay_day' => Carbon::now()->day,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        $business_pay_cycle_start = $start_date ? Carbon::parse($start_date)->subMonth()->format('Y-m-d') : ($last_pay_day ? Carbon::parse($last_pay_day)->format('Y-m-d') : Carbon::now()->subMonth()->format('Y-m-d'));
        $business_pay_cycle_end = $end_date ? Carbon::parse($end_date)->subDay()->format('Y-m-d') : Carbon::now()->subDay()->format('Y-m-d');
        $business_payslip_data = [
            'business_id' => $this->business->id,
            'schedule_date' => $this->className === self::MANUALLY_GENERATED_PAYSLIP ? $this->period : Carbon::now()->toDateString(),
            'cycle_start_date' => $start_date ? Carbon::parse($start_date)->subMonth()->format('Y-m-d') : ($last_pay_day ? Carbon::parse($last_pay_day)->format('Y-m-d') : Carbon::now()->subMonth()->format('Y-m-d')),
            'cycle_end_date' => $end_date ? Carbon::parse($end_date)->subDay()->format('Y-m-d') : Carbon::now()->subDay()->format('Y-m-d'),
            'status' => Status::PENDING,
        ];
        $business_payslip = $this->createBusinessPayslip($business_payslip_data);
        foreach ($this->businessMembers as $business_member) {
            try {
                if ($this->generatedBusinessMemberIds && in_array($business_member->id, $this->generatedBusinessMemberIds)) continue;
                $joining_date = $business_member->join_date;
                if ($last_pay_day && $joining_date <= Carbon::parse($last_pay_day) || !$last_pay_day && $joining_date <= Carbon::now()->subMonth()) $joining_date = null;
                $prorated_time_frame = null;
                $time_frame = $this->timeFrame->forDateRange($start_date, $end_date);
                if ($joining_date) {
                    $prorated_time_frame = app(TimeFrame::class);
                    $prorated_time_frame = $prorated_time_frame->forDateRange($joining_date, $end_date);
                    $total_policy_working_days = $this->getTotalBusinessWorkingDays($this->createPeriodByTime($joining_date, $end_date), $business_office);
                }
                $gross_salary_breakdown_percentage = $this->grossSalaryBreakdownCalculate->payslipComponentPercentageBreakdown($business_member);
                $payroll_component_calculation = $this->payrollComponentSchedulerCalculation
                    ->setBusiness($this->business)
                    ->setBusinessOffice($business_office)
                    ->setTotalWorkingDay($total_policy_working_days)
                    ->setBusinessMember($business_member)
                    ->setProratedTimeFrame($prorated_time_frame)
                    ->setTimeFrame($time_frame)
                    ->getPayrollComponentCalculationBreakdown();
                $gross_salary = 0.0;
                $salary = $business_member->salary;
                if ($salary) $gross_salary = floatValFormat($salary->gross_salary);
                $gross_salary_breakdown = $this->grossSalaryBreakdownCalculate
                    ->setBusiness($this->business)
                    ->setJoiningDate($joining_date)
                    ->setBusinessPayCycleStart($start_date)
                    ->setBusinessPayCycleEnd($end_date)
                    ->totalAmountPerComponent($gross_salary, $gross_salary_breakdown_percentage);
                $tax_gross_breakdown = $this->grossSalaryBreakdownCalculate->getGrossBreakdown();
                $taxable_payroll_component = $this->payrollComponentSchedulerCalculation->getTaxComponentData();
                $this->taxCalculator
                    ->setBusinessMember($business_member)
                    ->setGrossSalary($gross_salary)
                    ->setGrossSalaryBreakdown($tax_gross_breakdown)
                    ->setTaxableComponent($taxable_payroll_component)
                    ->calculate();
                $monthly_tax_amount = $this->taxCalculator->getMonthlyTaxAmount();
                $payroll_component_calculation['payroll_component']['deduction']['tax'] = $monthly_tax_amount;
                $tax_report_data = $this->taxCalculator->getBusinessMemberTaxHistoryData();
                $payslip_data = [
                    'business_payslip_id' => $business_payslip->id,
                    'business_member_id' => $business_member->id,
                    'schedule_date' => Carbon::now(),
                    'status' => Status::PENDING,
                    'generation_type' => 'auto',
                    'salary_breakdown' => json_encode(array_merge(['gross_salary_breakdown' => $gross_salary_breakdown], $payroll_component_calculation))
                ];
                if ($this->className === self::MANUALLY_GENERATED_PAYSLIP) {
                    $payslip_data['generation_type'] = 'manual';
                    $payslip_data['generated_for'] = $this->generatedFor;
                }
                if ($joining_date) $payslip_data['joining_log'] = Carbon::parse($joining_date)->format('Y-m-d');
                DB::transaction(function () use ($payslip_data) {
                    $this->payslipRepository->create($payslip_data);
                });
                if ($tax_report_data) {
                    DB::transaction(function () use ($tax_report_data) {
                        $this->taxHistoryRepository->create($tax_report_data);
                    });
                }
            } catch (Throwable $e) {
                app('sentry')->captureException($e);
            }
        }
        $package_generate_information = $this->payrollComponentSchedulerCalculation->getPackageGenerateData();
        if ($package_generate_information) $this->updatePackageGenerateDate($package_generate_information);
        $this->updatePayDay();
        $this->updateBusinessWorkingDaysHistory($business_working_day_data);
        $this->payrollPayDayHistoryUpdate($business_pay_day_data);
    }

    private function updatePackageGenerateDate($package_generate_information)
    {
        foreach ($package_generate_information as $package_id => $package_generate_data) {
            $package = $this->payrollComponentPackageRepository->find($package_id);
            DB::transaction(function () use ($package, $package_generate_data) {
                $this->payrollComponentPackageRepository->update($package, $package_generate_data);
            });
        }
    }

    private function updatePayDay()
    {
        $next_pay_day = $this->nextPayslipGenerationDay($this->business);
        $data['next_pay_day'] = $next_pay_day;
        $data['last_pay_day'] = $this->className === self::MANUALLY_GENERATED_PAYSLIP ? Carbon::parse($next_pay_day)->subMonth()->toDateString() : Carbon::now()->format('Y-m-d');
        $this->payrollSetting->update($data);
    }

    private function createBusinessPayslip(array $business_payslip_data)
    {
        return $this->businessPayslipRepo->create($business_payslip_data);
    }

    private function updateBusinessWorkingDaysHistory($business_working_day_data)
    {
        $this->businessWorkingDayRepository->create($business_working_day_data);
    }

    private function payrollPayDayHistoryUpdate($business_pay_day_data)
    {
        $this->payrollPayDayHistoryRepository->create($business_pay_day_data);
    }
}
