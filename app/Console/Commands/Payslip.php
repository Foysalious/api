<?php namespace App\Console\Commands;

use App\Models\Business;
use App\Sheba\Business\PayrollComponent\Components\Deductions\Tax\TaxCalculator;
use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdownCalculate;
use App\Sheba\Business\PayrollComponent\Components\PayrollComponentSchedulerCalculation;
use App\Sheba\Business\PayrollSetting\PayrollCommonCalculation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepo;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepo;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollComponentPackage\PayrollComponentPackageRepository;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use Sheba\Dal\PayrollSetting\PayrollSettingRepository;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\TaxHistory\TaxHistoryRepository;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;

class Payslip extends Command
{
    use ModificationFields, PayrollCommonCalculation;

    /** @var string The name and signature of the console command. */
    protected $signature = 'sheba:generate-payslips';

    /** @var string The console command description. */
    protected $description = 'Generate Payslips For Salary';

    private $payrollSettingRepository;
    private $payrollComponentRepository;
    private $grossSalaryBreakdownCalculate;
    private $payslipRepository;
    private $businessWeekRepo;
    private $businessHolidayRepo;
    private $payrollComponentSchedulerCalculation;
    private $payrollComponentPackageRepository;
    /** @var TaxCalculator */
    private $taxCalculator;
    private $timeFrame;
    /*** @var TaxHistoryRepository $taxHistoryRepository */
    private $taxHistoryRepository;


    /**
     * Payslip constructor.
     * @param PayrollSettingRepository $payroll_setting_repository
     * @param PayrollComponentRepository $payroll_component_repository
     * @param GrossSalaryBreakdownCalculate $gross_salary_breakdown_calculate
     * @param PayslipRepository $payslip_repository
     * @param BusinessWeekendRepo $business_weekend_repo
     * @param BusinessHolidayRepo $business_holiday_repo
     * @param PayrollComponentSchedulerCalculation $payroll_component_scheduler_calculation
     * @param TaxCalculator $tax_calculator
     * @param TaxHistoryRepository $tax_history_repository
     * @param TimeFrame $time_frame
     */
    public function __construct(PayrollSettingRepository $payroll_setting_repository,
                                PayrollComponentRepository $payroll_component_repository,
                                GrossSalaryBreakdownCalculate $gross_salary_breakdown_calculate,
                                PayslipRepository $payslip_repository,
                                BusinessWeekendRepo $business_weekend_repo,
                                BusinessHolidayRepo $business_holiday_repo,
                                PayrollComponentSchedulerCalculation $payroll_component_scheduler_calculation,
                                TaxCalculator $tax_calculator, TaxHistoryRepository $tax_history_repository, TimeFrame $time_frame)
    {
        $this->payrollSettingRepository = $payroll_setting_repository;
        $this->payrollComponentRepository = $payroll_component_repository;
        $this->grossSalaryBreakdownCalculate = $gross_salary_breakdown_calculate;
        $this->payslipRepository = $payslip_repository;
        $this->businessWeekRepo = $business_weekend_repo;
        $this->businessHolidayRepo = $business_holiday_repo;
        $this->payrollComponentSchedulerCalculation = $payroll_component_scheduler_calculation;
        $this->payrollComponentPackageRepository = app(PayrollComponentPackageRepository::class);
        $this->taxCalculator = $tax_calculator;
        $this->timeFrame = $time_frame;
        $this->taxHistoryRepository = $tax_history_repository;
        parent::__construct();
    }

    public function handle()
    {
        $payroll_settings = $this->payrollSettingRepository->where('is_enable', 1)->get();
        foreach ($payroll_settings as $payroll_setting) {
            $business = $payroll_setting->business;
            if ($this->isPayDay($payroll_setting)) {
                $business_members = $business->getAccessibleBusinessMember()->get();
                foreach ($business_members as $business_member) {
                    $joining_date = $business_member->join_date;
                    if ($joining_date <= Carbon::now()->subMonth()) $joining_date = null;
                    $start_date = $joining_date ? Carbon::parse($joining_date) : Carbon::now()->subMonth()->format('Y-m-d');
                    $end_date = Carbon::now()->subDay()->format('Y-m-d');
                    $prorated_time_frame = $this->timeFrame->forDateRange($start_date, $end_date);
                    $gross_salary_breakdown_percentage = $this->grossSalaryBreakdownCalculate->payslipComponentPercentageBreakdown($business_member);
                    $payroll_component_calculation = $this->payrollComponentSchedulerCalculation->setBusiness($business)->setBusinessMember($business_member)->setTimeFrame($prorated_time_frame)->getPayrollComponentCalculationBreakdown();
                    $gross_salary = 0.0;
                    $salary = $business_member->salary;
                    if ($salary) $gross_salary = floatValFormat($salary->gross_salary);
                    $gross_salary_breakdown = $this->grossSalaryBreakdownCalculate->setBusiness($business)->setJoiningDate($joining_date)->setBusinessPayCycleStart(Carbon::now()->subMonth()->format('Y-m-d'))->setBusinessPayCycleEnd($end_date)->totalAmountPerComponent($gross_salary, $gross_salary_breakdown_percentage);
                    $tax_gross_breakdown = $this->grossSalaryBreakdownCalculate->getGrossBreakdown();
                    $taxable_payroll_component = $this->payrollComponentSchedulerCalculation->getTaxComponentData();
                    $this->taxCalculator->setBusinessMember($business_member)->setGrossSalary($gross_salary)->setGrossSalaryBreakdown($tax_gross_breakdown)->setTaxableComponent($taxable_payroll_component)->calculate();
                    $monthly_tax_amount = $this->taxCalculator->getMonthlyTaxAmount();
                    $payroll_component_calculation['payroll_component']['deduction']['tax'] = $monthly_tax_amount;
                    $tax_report_data = $this->taxCalculator->getBusinessMemberTaxHistoryData();
                    $payslip_data = [
                        'business_member_id' => $business_member->id,
                        'schedule_date' => Carbon::now(),
                        'status' => 'pending',
                        'salary_breakdown' => json_encode(array_merge(['gross_salary_breakdown' => $gross_salary_breakdown], $payroll_component_calculation))
                    ];
                    if ($joining_date) $payslip_data['joining_log'] = Carbon::parse($joining_date)->format('Y-m-d');
                    DB::transaction(function () use ($payslip_data) {
                        $this->payslipRepository->create($payslip_data);
                    });
                    if ($tax_report_data) {
                        DB::transaction(function () use ($tax_report_data) {
                            $this->taxHistoryRepository->create($tax_report_data);
                        });
                    }
                }
                $package_generate_information = $this->payrollComponentSchedulerCalculation->getPackageGenerateData();
                if ($package_generate_information) $this->updatePackageGenerateDate($package_generate_information);
                $this->updatePayDay($payroll_setting, $business);
            }
        }
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

    private function updatePayDay(PayrollSetting $payroll_setting, Business $business)
    {
        $next_pay_day = $this->nextPayslipGenerationDay($business);
        $data['next_pay_day'] = $next_pay_day;
        $data['last_pay_day'] = Carbon::now()->format('Y-m-d');
        $payroll_setting->update($data);
    }
}
