<?php namespace App\Sheba\Business\Salary;

use App\Models\BusinessMember;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Salary\SalaryRepository;
use Sheba\Dal\SalaryLog\SalaryLogRepository;
use App\Sheba\Business\SalaryLog\Requester;
use App\Sheba\Business\SalaryLog\Creator;

class Updater
{
    private $salaryRequest;
    /** @var SalaryRepository */
    private $salaryRepository;
    private $businessMember;
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
    private $managerMember;
    private $oldSalary;

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
    }

    /** @param $salary_request */
    public function setSalaryRequester($salary_request)
    {
        $this->salaryRequest = $salary_request;
        return $this;
    }

    public function setSalary($salary)
    {
        $this->salary = $salary;
        return $this;
    }

    public function setOldSalary($old_salary)
    {
        $this->oldSalary = $old_salary;
        return $this;
    }

    public function setManagerMember(Member $manager_member)
    {
        $this->managerMember = $manager_member;
        return $this;
    }

    public function update()
    {
        $this->makeData();
        DB::transaction(function () {
            $this->salaryRepository->update($this->salary, $this->salaryData);
            $this->salaryLogCreate($this->salary);
        });
    }

    private function makeData()
    {
        $this->salaryData['gross_salary'] = $this->salaryRequest->getGrossSalary();
    }

    private function salaryLogCreate($salary)
    {
        $this->salaryLogRequester->setBusinessMember($this->salaryRequest->getBusinessMember())->setSalaryRequest($this->salaryRequest)->setSalary($salary);
        $this->salaryLogCreator->setOldSalary($this->oldSalary)->setSalaryLogRequester($this->salaryLogRequester)->create();
    }

}
