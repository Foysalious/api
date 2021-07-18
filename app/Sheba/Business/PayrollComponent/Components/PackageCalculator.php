<?php namespace App\Sheba\Business\PayrollComponent\Components;


use App\Models\BusinessMember;
use App\Sheba\Business\ComponentPackage\Formatter;
use App\Sheba\Business\PayrollSetting\PayrollCommonCalculation;
use Carbon\Carbon;
use Sheba\Dal\PayrollComponent\TargetType as ComponentTargetType;
use Sheba\Dal\PayrollComponentPackage\CalculationType;
use Sheba\Dal\PayrollComponentPackage\PayrollComponentPackage;
use Sheba\Dal\PayrollComponentPackage\PayrollComponentPackageRepository;
use Sheba\Dal\PayrollComponentPackage\ScheduleType;
use Sheba\Dal\PayrollSetting\PayrollSetting;

class PackageCalculator
{
    const FIXED_AMOUNT = 'fixed_amount';
    const GROSS_SALARY = 'gross';

    use PayrollCommonCalculation;

    /** @var BusinessMember */
    private $businessMember;
    /*** @var PayrollSetting */
    private $payrollSetting;
    /** @var PayrollComponentPackage */
    private $package;
    /*** @var PayrollComponentPackageRepository */
    private $payrollComponentPackageRepository;
    private $packageGenerateData = [];

    public function __construct()
    {
        $this->payrollComponentPackageRepository = app(PayrollComponentPackageRepository::class);
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }
    public function setPayrollSetting(PayrollSetting $payroll_setting)
    {
        $this->payrollSetting = $payroll_setting;
        return $this;
    }
    public function setPackage(PayrollComponentPackage $package)
    {
        $this->package = $package;
        return $this;
    }
    public function calculate()
    {
        $calculation_type = $this->package->calculation_type; // Package Calculation Type -> FIX PAY or VARIABLE AMOUNT
        $on_what = $this->package->on_what; // Package Calculation on which Gross Salary Component
        $amount = floatValFormat($this->package->amount); // Package Amount Percentage or Fixed Amount of tk
        $schedule_type = $this->package->schedule_type; // Package Schedule Type Periodically or Fixed Month
        $schedule_date = $this->package->schedule_date; // On which month Package should be availed

        $final_amount = 0;
        if ($calculation_type == CalculationType::VARIABLE_AMOUNT) return $final_amount;
        $current_time = Carbon::now();
        //$business_member_salary = $this->businessMember->salary ? floatValFormat($this->businessMember->salary->gross_salary) : 0;
        if ($schedule_type == ScheduleType::FIXED_DATE && $current_time->month != $schedule_date) return $final_amount;
        $next_generated_month = Carbon::parse($this->package->generated_at)->format('Y-m'); // Calculate Package Generation Day Based on Last Generated Day
        if ($schedule_type == ScheduleType::PERIODICALLY && $next_generated_month != $current_time->format('Y-m')) return $final_amount;
        if ($calculation_type == CalculationType::FIX_PAY_AMOUNT) {
            $final_amount = $this->getFixPayAmountCalculation($this->businessMember, $this->package, $on_what, $amount);
        }
        //dump($this->businessMember->id.' = '.$this->package->name.' = '.$schedule_type);
        if ($schedule_type != ScheduleType::PERIODICALLY) return $final_amount;
        if (!array_key_exists($this->package->id, $this->packageGenerateData)) {
            $this->packageGenerateData[$this->package->id] = (new Formatter)->packageGenerateData($this->payrollSetting, $current_time->format('Y-m-d'), $this->package->periodic_schedule);
        }
        //dd($this->packageGenerateData);
        return $final_amount;
    }
    public function getPackageGenerateData() {
        return $this->packageGenerateData;
    }

}