<?php namespace App\Sheba\Business\Salary;

use App\Models\BusinessMember;
use App\Sheba\Business\Salary\Component\Maker;
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
            $this->createComponentPercentage();
        });
        return true;
    }

    private function makeData()
    {
        $this->salaryData['business_member_id'] = $this->salaryRequest->getBusinessMember()->id;
        $this->salaryData['gross_salary'] = $this->salaryRequest->getGrossSalary();
    }

    private function createComponentPercentage()
    {
        $business_member = $this->salaryRequest->getBusinessMember();
        $payroll_setting = $business_member->business->payrollSetting;
        foreach ($this->salaryRequest->getBreakdownPercentage() as $component) {
            $gross_salary_breakdown_maker = new Maker($component);
            $existing_payroll_component = $this->payrollComponentRepository->where('name', $component['name'])->where('payroll_setting_id', $payroll_setting->id)->first();
            $gross_salary_breakdown_maker->setBusinessMember($business_member)
                ->setManagerMember($this->salaryRequest->getManagerMember())
                ->setOldSalaryAmount($this->salaryRequest->getGrossSalary())
                ->setPayrollComponent($existing_payroll_component)
                ->setPayrollSetting($payroll_setting)
                ->createCoWorkerGrossComponent();
        }
    }

}
