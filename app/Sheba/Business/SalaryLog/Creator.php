<?php namespace App\Sheba\Business\SalaryLog;

use Sheba\Dal\SalaryLog\SalaryLogRepository;

class Creator
{
    /** @var Requester */
    private $salaryLogRequester;
    private $salaryLogData = [];
    /** @var SalaryLogRepository */
    private $salaryLogRepository;

    public function __construct(SalaryLogRepository $salary_log_repository)
    {
        $this->salaryLogRepository = $salary_log_repository;
    }

    public function setSalaryLogRequester(Requester $salary_log_requester)
    {
        $this->salaryLogRequester = $salary_log_requester;
        return $this;
    }

    public function create()
    {
        $this->makeData();
        $this->salaryLogRepository->insert($this->salaryLogData);
    }

    private function makeData()
    {
        $this->salaryLogData['salary_id'] = $this->salaryLogRequester->getSalary()->id;
        $this->salaryLogData['new'] = $this->salaryLogRequester->getSalaryRequest()->getGrossSalary();
        $this->salaryLogData['old'] = $this->salaryLogRequester->getSalary()->gross_salary;
        $this->salaryLogData['log'] = 'User changed Salary '. $this->salaryLogRequester->getSalary()->gross_salary . ' to '.$this->salaryLogRequester->getSalaryRequest()->getGrossSalary();
    }
}
