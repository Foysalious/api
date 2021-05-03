<?php namespace App\Sheba\Business\Salary;

use App\Models\BusinessMember;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\Salary\SalaryRepository;

class Creator
{
    /** @var Requester */
    private $salaryRequest;
    private $salaryData = [];
    private $businessMember;
    /** @var SalaryRepository */
    private $salaryRepository;
    /*** @var PayrollComponentRepository*/
    private $payrollComponentRepository;

    /**
     * Updater constructor.
     * @param SalaryRepository $salary_repository
     */
    public function __construct(SalaryRepository $salary_repository)
    {
        $this->salaryRepository = $salary_repository;
        $this->payrollComponentRepository = app(PayrollComponentRepository::class);
    }

    /** @param $salary_request */
    public function setSalaryRequester(Requester $salary_request)
    {
        $this->salaryRequest = $salary_request;
        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function create()
    {
        $this->makeData();
        DB::transaction(function () {
            $this->salaryRepository->create($this->salaryData);
            //$this->createComponentPercentage();
        });
        return true;
    }

    private function makeData()
    {
        $this->salaryData['business_member_id'] = $this->salaryRequest->getBusinessMember()->id;
        $this->salaryData['gross_salary'] = $this->salaryRequest->getGrossSalary();
    }

    public function createComponentPercentage()
    {
        $payroll_Setting = $this->businessMember->business->payrollSetting;
        dd($payroll_Setting);
    }

}
