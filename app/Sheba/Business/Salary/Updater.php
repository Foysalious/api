<?php namespace App\Sheba\Business\Salary;

use App\Models\BusinessMember;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Salary\SalaryRepository;
use Sheba\Dal\SalaryLog\SalaryLogRepository;
use App\Sheba\Business\SalaryLog\Requester;
use App\Sheba\Business\SalaryLog\Creator;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;
    private $salaryRequest;
    /** @var SalaryRepository */
    private $salaryRepositry;
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

    /**
     * Updater constructor.
     * @param SalaryRepository $salary_repositry
     * @param SalaryLogRepository $salary_log_repository
     * @param Requester $salary_log_requester
     * @param Creator $salary_log_creator
     */
    public function __construct(SalaryRepository $salary_repositry, SalaryLogRepository $salary_log_repository, Requester $salary_log_requester, Creator $salary_log_creator)
    {
        $this->salaryRepositry = $salary_repositry;
        $this->SalaryLogRepository = $salary_log_repository;
        $this->salaryLogRequester = $salary_log_requester;
        $this->salaryLogCreator = $salary_log_creator;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        dd($this->businessMember);
        $this->businessMember = $business_member;
        return $this;
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

    public function update()
    {
        $this->makeData();
        DB::transaction(function () {
            $this->salaryRepositry->update($this->salary, $this->salaryData);
            $this->SalaryLogCreate($this->salary);
        });
    }

    private function makeData()
    {
        $this->salaryData['gross_salary'] = $this->salaryRequest->getGrossSalary();
    }

    private function SalaryLogCreate($salary)
    {
        dd($this->businessMember);
        $this->setModifier($this->businessMember);
        $this->salaryLogRequester->setBusinessMember($this->businessMember)->setSalaryRequest($this->salaryRequest)->setSalary($salary);
        $this->salaryLogCreator->setSalaryLogRequester($this->salaryLogRequester)->create();
    }

}
