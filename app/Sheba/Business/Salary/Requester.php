<?php namespace App\Sheba\Business\Salary;


use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Sheba\Business\Salary\Creator as CoWorkerSalaryCreator;
use App\Sheba\Business\Salary\Updater as CoWorkerSalaryUpdater;
use Sheba\Business\CoWorker\Statuses;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class Requester
{
    /** @var Business */
    private $business;
    private $grossSalary;
    private $businessMember;
    private $member;
    private $profile;
    /**
     * @var BusinessMemberRepositoryInterface
     */
    private $businessMemberRepository;
    private $creator;
    private $updater;
    private $managerMember;

    public function __construct(BusinessMemberRepositoryInterface $business_member_repository,
                                CoWorkerSalaryCreator $salary_creator,
                                CoWorkerSalaryUpdater $salary_updater)
    {
        $this->businessMemberRepository = $business_member_repository;
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

    public function createOrUpdate()
    {
        $salary = $this->businessMember->salary;
        if (!$salary) {
            $this->creator->setSalaryRequester($this)->create();
        }else{
            $this->updater->setSalary($salary)->setSalaryRequester($this)->update();
        }
    }
}
