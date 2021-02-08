<?php namespace App\Sheba\Business\SalaryLog;

use Sheba\Dal\SalaryLog\SalaryLogRepository;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;
    /** @var Requester */
    private $salaryLogRequester;
    private $salaryLogData = [];
    /** @var SalaryLogRepository */
    private $salaryLogRepository;
    private $oldSalary;

    public function __construct(SalaryLogRepository $salary_log_repository)
    {
        $this->salaryLogRepository = $salary_log_repository;
    }

    public function setSalaryLogRequester(Requester $salary_log_requester)
    {
        $this->salaryLogRequester = $salary_log_requester;
        return $this;
    }

    public function setOldSalary($old_salary)
    {
        $this->oldSalary = $old_salary;
        return $this;
    }

    public function create()
    {
        $this->makeData();
        $this->salaryLogRepository->create($this->withCreateModificationField($this->salaryLogData));
    }

    private function makeData()
    {
        $this->salaryLogData['salary_id'] = $this->salaryLogRequester->getSalary()->id;
        $this->salaryLogData['new'] = $this->salaryLogRequester->getSalaryRequest()->getGrossSalary();
        $this->salaryLogData['old'] = $this->oldSalary;
        $this->salaryLogData['log'] = $this->getMember().' changed Salary '. (float) $this->oldSalary . ' to '.(float) $this->salaryLogRequester->getSalaryRequest()->getGrossSalary();
    }

    private function getMember()
    {
        $member = $this->salaryLogRequester->getBusinessMember()->member;
        return $member ? $member->profile ? $member->profile->name : null : null;
    }
}
