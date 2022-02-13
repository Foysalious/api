<?php namespace App\Jobs\Business;

use App\Jobs\Job;
use App\Models\Business;
use App\Sheba\Business\BusinessQueue;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\Dal\GrossSalaryBreakdownHistory\GrossSalaryBreakdownHistoryRepository;
use Sheba\Dal\PayrollComponent\TargetType;
use Sheba\Dal\PayrollComponent\Type;

class UpdateGlobalGrossSalaryHistory extends Job implements ShouldQueue
{
    const INDIVIDUAL_SALARY = 'individual_salary';
    const BREAKDOWN_GROSS_SALARY = 'breakdown_gross_salary';

    private $payrollSetting;
    private $grossComponentDelete;
    /*** @var GrossSalaryBreakdownHistoryRepository $grossSalaryBreakdownHistoryRepository*/
    private $grossSalaryBreakdownHistoryRepository;
    /*** @var Business $business*/
    private $business;

    public function __construct($payroll_setting, $gross_component_delete)
    {
        $this->grossSalaryBreakdownHistoryRepository = app(GrossSalaryBreakdownHistoryRepository::class);
        $this->payrollSetting = $payroll_setting;
        $this->business = $payroll_setting->business;
        $this->grossComponentDelete = json_decode($gross_component_delete, 1);
    }

    public function handle()
    {
        $data = $this->makeData();
        $active_business_members = $this->business->getActiveBusinessMember()->get();
        foreach ($active_business_members as $business_member) {
            $existing_setting = $this->grossSalaryBreakdownHistoryRepository->where('business_member_id', $business_member->id)->where('setting_form_where', self::BREAKDOWN_GROSS_SALARY)->where('end_date', null)->first();
            $this->grossSalaryBreakdownHistoryRepository->update($existing_setting, ['end_date' => Carbon::now()->toDateString()]);
            $this->grossSalaryBreakdownHistoryRepository->create([
                'business_member_id' => $business_member->id,
                'setting_form_where' => self::BREAKDOWN_GROSS_SALARY,
                'settings' => json_encode($data),
                'start_date' => Carbon::now()->toDateString(),
            ]);
        }
    }

    private function makeData()
    {
        $data = [];
        $payroll_components = $this->payrollSetting->components()->where('type', Type::GROSS)->where(function ($query) {
            return $query->where('target_type', null)->orWhere('target_type', TargetType::GENERAL);
        })->where(function ($query) {
            return $query->where('is_default', 1)->orWhere('is_active', 1);
        })->orderBy('type')->get();
        foreach ($payroll_components as $component) {
            $data[] = [
                'id' => $component->id,
                'name' => $component->name,
                'value' => json_decode($component->setting, 1)['percentage'],
                'is_taxable' => $component->is_taxable,
                'is_active' => $component->is_active,
                'is_deleted' => 0
            ];
        }
        if ($this->grossComponentDelete) {
            foreach ($this->grossComponentDelete as $component) {
                $existing_component = $this->payrollSetting->findWithTrashed($component);
                $data[] = [
                    'id' => $existing_component->id,
                    'name' => $existing_component->name,
                    'value' => json_decode($existing_component->setting, 1)['percentage'],
                    'is_taxable' => $existing_component->is_taxable,
                    'is_active' => $existing_component->is_active,
                    'is_deleted' => 1
                ];
            }
        }
        return $data;
    }

}