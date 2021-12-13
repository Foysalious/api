<?php namespace App\Console\Commands;

use App\Models\Business;
use App\Sheba\Business\Payslip\BusinessWisePayslip;
use Carbon\Carbon;
use Sheba\Dal\Payslip\PayslipRepository;

class GeneratePayslip extends Command
{
    protected $signature = 'sheba:generate-business-payslips {business_id} {year} {month}';
    /*** @var PayslipRepository $payslipRepository*/
    private $payslipRepository;

    public function __construct()
    {
        $this->payslipRepository = app(PayslipRepository::class);
        parent::__construct();
    }

    public function handle()
    {
        $month = $this->argument('month');
        $year = $this->argument('year');
        $business_id = $this->argument('business_id');
        $business = Business::find($business_id);
        $payroll_setting = $business->payrollSetting;
        $pay_day_type = $payroll_setting->pay_day_type;
        $pay_day = 1;
        if ($pay_day_type == 'fixed_date') $pay_day = $payroll_setting->pay_day;
        if ($pay_day_type == 'last_working_day') $pay_day = Carbon::now()->month($month)->lastOfMonth()->day;
        $period = Carbon::create($year, $month, $pay_day)->toDateString();
        $active_business_members = $business->getActiveBusinessMember();
        $active_business_member_ids = $active_business_members;
        $generated_business_member = $this->payslipRepository
            ->where('status', 'pending')->where('schedule_date', 'like', "%$period%")
            ->whereIn('business_member_id', $active_business_member_ids->pluck('id')->toArray())
            ->pluck('business_member_id')->toArray();
        $business_wise_payslips = new BusinessWisePayslip();
        $business_wise_payslips->setBusiness($business)
            ->setBusinessMember($active_business_members->get())
            ->setPayrollSetting($payroll_setting)
            ->setGeneratePeriod($period)
            ->setGeneratedBusinessMemberIds($generated_business_member)
            ->calculate();
    }

}