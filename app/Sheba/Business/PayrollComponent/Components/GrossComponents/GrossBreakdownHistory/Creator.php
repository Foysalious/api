<?php namespace App\Sheba\Business\PayrollComponent\Components\GrossComponents\GrossBreakdownHistory;

use Carbon\Carbon;
use Sheba\Dal\GrossSalaryBreakdownHistory\GrossSalaryBreakdownHistoryRepository;

class Creator
{

    const INDIVIDUAL_SALARY = 'individual_salary';
    const BREAKDOWN_GROSS_SALARY = 'breakdown_gross_salary';
    private $businessMember;
    private $grossSalaryBreakdown;
    /*** @var GrossSalaryBreakdownHistoryRepository $grossSalaryBreakdownHistoryRepository*/
    private $grossSalaryBreakdownHistoryRepository;

    public function __construct()
    {
        $this->grossSalaryBreakdownHistoryRepository = app(GrossSalaryBreakdownHistoryRepository::class);
    }

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setGrossSalaryBreakdown($gross_salary)
    {
        $this->grossSalaryBreakdown = json_decode($gross_salary,1);
        return $this;
    }

    public function update()
    {
        $data = [];
        foreach ($this->grossSalaryBreakdown as $breakdown) {
            $data [] = [
                'id' => $breakdown['id'],
                'name' => $breakdown['name'],
                'value' => $breakdown['value'],
                'is_taxable' => $breakdown['is_default'] ? 1 : $breakdown['is_taxable'],
                'is_active' => $breakdown['is_default'] ? 1 : $breakdown['is_active']
            ];
        }
        $existing_setting = $this->grossSalaryBreakdownHistoryRepository->where('business_member_id', $this->businessMember->id)->where('end_date', null)->first();
        $this->grossSalaryBreakdownHistoryRepository->update($existing_setting, ['end_date' => Carbon::now()->toDateString()]);
        $this->grossSalaryBreakdownHistoryRepository->create([
            'business_member_id' => $this->businessMember->id,
            'setting_form_where' => self::INDIVIDUAL_SALARY,
            'settings' => json_encode($data),
            'start_date' => Carbon::now()->toDateString(),
        ]);
    }

}