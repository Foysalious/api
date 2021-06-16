<?php namespace App\Sheba\Business\SalaryLog\ComponentBreakdownLog;

use Sheba\Dal\SalaryLog\SalaryLogRepository;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;

    /*** @var Requester $componentBreakdownLogRequester*/
    private $componentBreakdownLogRequester;
    /**  @var SalaryLogRepository $salaryLogRepository */
    private $salaryLogRepository;
    private $componentBreakdownLogData = [];

    public function __construct()
    {
        $this->salaryLogRepository = app(SalaryLogRepository::class);
    }

    public function setComponentBreakdownLogRequester(Requester $component_breakdown_log_requester)
    {
        $this->componentBreakdownLogRequester = $component_breakdown_log_requester;
        return $this;
    }

    public function create()
    {
        $this->makeData();
        $this->salaryLogRepository->create($this->withCreateModificationField($this->componentBreakdownLogData));
    }

    private function makeData()
    {
        $this->componentBreakdownLogData['salary_id'] = $this->componentBreakdownLogRequester->getSalary()->id;
        $this->componentBreakdownLogData['new'] = floatValFormat($this->componentBreakdownLogRequester->getComponentAmount());
        $this->componentBreakdownLogData['old'] = floatValFormat($this->componentBreakdownLogRequester->getOldAmount());
        $this->componentBreakdownLogData['log'] = $this->getManagerMember() . ' changed "'.$this->componentBreakdownLogRequester->getComponentTitle() .'" from "'. floatValFormat($this->componentBreakdownLogRequester->getOldPercentage()) . '%" ("৳'.floatValFormat($this->componentBreakdownLogRequester->getOldAmount()).'") to "' . floatValFormat($this->componentBreakdownLogRequester->getComponentPercentage()). '%" ("৳'.floatValFormat($this->componentBreakdownLogRequester->getComponentAmount()).'")';
    }

    private function getManagerMember()
    {
        $member = $this->componentBreakdownLogRequester->getManagerMember();
        $profile = $member->profile;
        return $profile ? $profile->name : null;
    }

}