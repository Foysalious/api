<?php namespace Sheba\Business\Salary;

use App\Models\BusinessMember;
use Sheba\Business\Salary\Creator as CoWorkerSalaryCreator;
use App\Sheba\Business\Salary\Updater as CoWorkerSalaryUpdater;

class Requester
{
    private $grossSalary;
    private $businessMember;
    private $member;
    private $profile;
    private $creator;
    private $updater;
    private $managerMember;
    private $breakdownPercentage;
    private $removeOverwritten;
    private $isBulkGrossSalary;

    public function __construct(CoWorkerSalaryCreator $salary_creator, CoWorkerSalaryUpdater $salary_updater)
    {
        $this->creator = $salary_creator;
        $this->updater = $salary_updater;
    }

    public function setMember($member)
    {
        $this->member = Member::findOrFail($member);
        $this->profile = $this->member->profile;
        $this->businessMember = $this->member->businessMember;

        if (!$this->businessMember) {
            $this->businessMember = $this->businessMemberRepository->builder()
                ->where('business_id', $this->business->id)
                ->where('member_id', $this->member->id)
                ->where('status', Statuses::INACTIVE)
                ->first();
        }

        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->profile = $this->businessMember->profile();
        return $this;
    }

    public function getMember()
    {
        return $this->member;
    }

    public function getBusinessMember()
    {
        return $this->businessMember;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param $gross_salary
     * @return $this
     */
    public function setGrossSalary($gross_salary)
    {
        $this->grossSalary = $gross_salary;
        if (!$this->grossSalary) $this->grossSalary = 0;
        return $this;
    }

    public function setBreakdownPercentage($breakdown_percentage)
    {
        $this->breakdownPercentage = json_decode($breakdown_percentage, 1);
        return $this;
    }

    public function getBreakdownPercentage()
    {
        return $this->breakdownPercentage;
    }

    public function setRemoveOverwritten($remove_overwritten)
    {
        $this->removeOverwritten = json_decode($remove_overwritten, 1);
        return $this;
    }

    public function getRemoveOverwritten()
    {
        return $this->removeOverwritten;
    }

    public function getGrossSalary()
    {
        return $this->grossSalary;
    }

    public function setManagerMember($manager_member)
    {
        $this->managerMember = $manager_member;
        return $this;
    }

    public function getManagerMember()
    {
        return $this->managerMember;
    }

    public function setIsForBulkGrossSalary($is_bulk_gross_salary)
    {
        $this->isBulkGrossSalary = $is_bulk_gross_salary;
        return $this;
    }

    public function getIsForBulkGrossSalary()
    {
        return $this->isBulkGrossSalary;
    }

    public function createOrUpdate()
    {
        $salary = $this->businessMember->salary;
        if (!$salary) {
            $this->creator->setSalaryRequester($this)->create();
        } else {
            $this->updater->setSalary($salary)->setSalaryRequester($this)->update();
        }
    }
}
