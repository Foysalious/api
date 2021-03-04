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

    public function create()
    {
        $this->makeData();
        $this->salaryLogRepository->create($this->withCreateModificationField($this->salaryLogData));
    }

    private function makeData()
    {
        $this->salaryLogData['salary_id'] = $this->salaryLogRequester->getSalary()->id;
        $this->salaryLogData['new'] = floatValFormat($this->salaryLogRequester->getGrossSalary());
        $this->salaryLogData['old'] = floatValFormat($this->salaryLogRequester->getOldSalary());
        $this->salaryLogData['log'] = $this->getManagerMember() . ' changed Salary ' . floatValFormat($this->salaryLogRequester->getOldSalary()) . ' to ' . floatValFormat($this->salaryLogRequester->getGrossSalary());
    }

    private function getManagerMember()
    {
        $member = $this->salaryLogRequester->getManagerMember();
        $profile = $member->profile;
        return $profile ? $profile->name : null;
    }
}
