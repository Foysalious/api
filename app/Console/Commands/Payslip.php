<?php namespace App\Console\Commands;

use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdownCalculate;
use App\Sheba\Business\PayrollComponent\Components\PayrollComponentCalculationForPayslip;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepo;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepo;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollSetting\PayrollSettingRepository;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use Sheba\Dal\PayrollSetting\PayDayType;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\ModificationFields;
use App\Models\Business;
use Carbon\Carbon;

class Payslip extends Command
{
    use ModificationFields;

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
    private $payrollComponentCalculationForPayslip;


    /**
     * Payslip constructor.
     * @param PayrollSettingRepository $payroll_setting_repository
     * @param PayrollComponentRepository $payroll_component_repository
     * @param GrossSalaryBreakdownCalculate $gross_salary_breakdown_calculate
     * @param PayslipRepository $payslip_repository
     * @param BusinessWeekendRepo $business_weekend_repo
     * @param BusinessHolidayRepo $business_holiday_repo
     * @param PayrollComponentCalculationForPayslip $payroll_component_calculation_for_payslip
     */
    public function __construct(PayrollSettingRepository $payroll_setting_repository,
                                PayrollComponentRepository $payroll_component_repository,
                                GrossSalaryBreakdownCalculate $gross_salary_breakdown_calculate,
                                PayslipRepository $payslip_repository,
                                BusinessWeekendRepo $business_weekend_repo,
                                BusinessHolidayRepo $business_holiday_repo,
                                PayrollComponentCalculationForPayslip $payroll_component_calculation_for_payslip)
    {
        $this->payrollSettingRepository = $payroll_setting_repository;
        $this->payrollComponentRepository = $payroll_component_repository;
        $this->grossSalaryBreakdownCalculate = $gross_salary_breakdown_calculate;
        $this->payslipRepository = $payslip_repository;
        $this->businessWeekRepo = $business_weekend_repo;
        $this->businessHolidayRepo = $business_holiday_repo;
        $this->payrollComponentCalculationForPayslip = $payroll_component_calculation_for_payslip;
        parent::__construct();
    }

    public function handle()
    {
        $payroll_settings = $this->payrollSettingRepository->where('is_enable', 1)->get();
        foreach ($payroll_settings as $payroll_setting) {
            /** @var Business $business */
            $business = $payroll_setting->business;
            if ($this->isPayDay($payroll_setting, $business)) {
                $business_members = $business->getAccessibleBusinessMember()->select('id')->get();
                $this->grossSalaryBreakdownCalculate->componentPercentageBreakdown($payroll_setting);
                $payroll_component_calculation = $this->payrollComponentCalculationForPayslip->setBusiness($business)->getPayrollComponentCalculationBreakdown();
                foreach ($business_members as $business_member) {
                    $gross_salary = 0.0;
                    $salary = $business_member->salary;
                    if ($salary) $gross_salary = floatValFormat($salary->gross_salary);
                    $this->grossSalaryBreakdownCalculate->totalAmountPerComponent($gross_salary);
                    $payslip_data = [
                        'business_member_id' => $business_member->id,
                        'schedule_date' => Carbon::now(),
                        'status' => 'pending',
                        'salary_breakdown' => json_encode(array_merge($this->grossSalaryBreakdownCalculate->totalAmountPerComponentFormatted(), $payroll_component_calculation))
                    ];
                    $this->payslipRepository->create($payslip_data);
                }
            }
        }
    }

    /**
     * @param PayrollSetting $payroll_setting
     * @param Business $business
     * @return bool
     */
    private function isPayDay(PayrollSetting $payroll_setting, Business $business)
    {
        if ($payroll_setting->pay_day_type == PayDayType::FIXED_DATE && Carbon::now()->day == $payroll_setting->pay_day) return true;
        $last_day_of_month = Carbon::now()->lastOfMonth();
        while ($last_day_of_month) {
            if (!$this->businessWeekRepo->isWeekendByBusiness($business, $last_day_of_month) &&
                !$this->businessHolidayRepo->isHolidayByBusiness($business, $last_day_of_month)) break;
            $last_day_of_month = $last_day_of_month->subDay(1);
        }
        return $last_day_of_month->isToday();
    }
}
