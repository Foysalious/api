<?php namespace App\Console\Commands;

use App\Sheba\Business\PayrollComponent\Components\Deductions\Tax\TaxCalculator;
use App\Sheba\Business\PayrollSetting\PayrollCommonCalculation;
use App\Sheba\Business\Payslip\BusinessWisePayslip;
use Sheba\Dal\PayrollSetting\PayrollSettingRepository;
use Sheba\Dal\TaxHistory\TaxHistoryRepository;
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
     */
    public function __construct(PayrollSettingRepository $payroll_setting_repository)
    {
        $this->payrollSettingRepository = $payroll_setting_repository;
        parent::__construct();
    }

    public function handle()
    {
        $payroll_settings = $this->payrollSettingRepository->where('is_enable', 1)->get();
        foreach ($payroll_settings as $payroll_setting) {
            $business = $payroll_setting->business;
            if ($this->isPayDay($payroll_setting)) {
                $active_business_members = $business->getActiveBusinessMember()->get();
                $business_wise_payslips = new BusinessWisePayslip();
                $business_wise_payslips->setBusiness($business)->setPayrollSetting($payroll_setting)->setBusinessMember($active_business_members)->calculate();
            }
        }
    }
}
