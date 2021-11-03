<?php namespace App\Sheba\Business\Salary;

use App\Sheba\Business\Salary\Component\Maker;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\Salary\SalaryRepository;
use Sheba\Dal\SalaryLog\SalaryLogRepository;
use App\Sheba\Business\SalaryLog\Requester;
use App\Sheba\Business\Salary\Requester as SalaryRequester;
use App\Sheba\Business\SalaryLog\Creator;

class Updater
{
    private $salaryRequest;
    /** @var SalaryRepository */
    private $salaryRepository;
    private $salaryData = [];
    private $salary;
    /** @var SalaryLogRepository */
    private $SalaryLogRepository;
    /**
     * @var Requester
     */
    private $salaryLogRequester;
    /** @var Creator */
    private $salaryLogCreator;
    private $oldSalary;
    /*** @var PayrollComponentRepository */
    private $payrollComponentRepository;

    /**
     * Updater constructor.
     * @param SalaryRepository $salary_repository
     * @param SalaryLogRepository $salary_log_repository
     * @param Requester $salary_log_requester
     * @param Creator $salary_log_creator
     */
    public function __construct(SalaryRepository $salary_repository, SalaryLogRepository $salary_log_repository, Requester $salary_log_requester, Creator $salary_log_creator)
    {
        $this->salaryRepository = $salary_repository;
        $this->SalaryLogRepository = $salary_log_repository;
        $this->salaryLogRequester = $salary_log_requester;
        $this->salaryLogCreator = $salary_log_creator;
        $this->payrollComponentRepository = app(PayrollComponentRepository::class);
    }

    /**
     * @param SalaryRequester $salary_request
     * @return $this
     */
    public function setSalaryRequester(SalaryRequester $salary_request)
    {
        $this->salaryRequest = $salary_request;
        return $this;
    }

    public function setSalary($salary)
    {
        $this->salary = $salary;
        $this->oldSalary = $this->salary->gross_salary;
        return $this;
    }

    public function update()
    {
        $this->makeData();
        DB::transaction(function () {
            if ($this->oldSalary != $this->salaryRequest->getGrossSalary()) {
                $this->salaryRepository->update($this->salary, $this->salaryData);
                $this->salaryLogCreate();
            }
            if (!$this->salaryRequest->getIsForBulkGrossSalary()) $this->createComponentPercentage();
        });
        return true;
    }

    private function makeData()
    {
        $this->salaryData['gross_salary'] = $this->salaryRequest->getGrossSalary();
    }

    private function salaryLogCreate()
    {
        $this->salary->fresh();
        $this->salaryLogRequester->setBusinessMember($this->salaryRequest->getBusinessMember())
            ->setGrossSalary($this->salaryRequest->getGrossSalary())
            ->setOldSalary($this->oldSalary)
            ->setManagerMember($this->salaryRequest->getManagerMember())
            ->setSalary($this->salary);
        $this->salaryLogCreator->setSalaryLogRequester($this->salaryLogRequester)->create();
    }
    private function createComponentPercentage()
    {
        $business_member = $this->salaryRequest->getBusinessMember();
        $breakdown_percentage = $this->salaryRequest->getBreakdownPercentage();
        if (empty($breakdown_percentage)) return true;
        foreach ($breakdown_percentage as $component) {
            $gross_salary_breakdown_maker = new Maker($component, $business_member, $this->salary, $this->oldSalary);
            $gross_salary_breakdown_maker->setManager($this->salaryRequest->getManagerMember());
            $gross_salary_breakdown_maker->runComponent();
        }
    }
}
