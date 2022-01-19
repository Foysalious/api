<?php namespace App\Sheba\Business\Salary\History;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\GrossSalaryHistory\GrossSalaryHistoryRepository;

class Creator
{
    /*** @var GrossSalaryHistoryRepository $grossSalaryHistoryRepository*/
    private $grossSalaryHistoryRepository;
    private $businessMember;
    private $managerMember;
    private $grossSalary;

    public function __construct()
    {
        $this->grossSalaryHistoryRepository = app(GrossSalaryHistoryRepository::class);
    }

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setManagerMember($manager_member)
    {
        $this->managerMember = $manager_member;
        return $this;
    }

    public function setGrossSalary($gross_salary)
    {
        $this->grossSalary = $gross_salary;
        return $this;
    }

    public function update()
    {
        $existing_settings = $this->grossSalaryHistoryRepository->where('business_member_id', $this->businessMember->id)->where('end_date', null)->first();
        DB::beginTransaction();
        $this->grossSalaryHistoryRepository->update($existing_settings, ['end_date' => Carbon::now()->toDateString()]);
        $this->grossSalaryHistoryRepository->create([
            'business_member_id' => $this->businessMember->id,
            'salary' => $this->grossSalary,
            'start_date' => Carbon::now()->toDateString()
        ]);
        DB::commit();
    }

}