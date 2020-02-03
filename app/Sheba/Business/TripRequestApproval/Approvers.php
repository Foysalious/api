<?php namespace Sheba\Business\TripRequestApproval;


use App\Models\Business;
use App\Models\BusinessMember;
use Sheba\Dal\TripRequestApprovalFlow\Model as TripRequestApprovalFlow;

class Approvers
{
    /** @var TripRequestApprovalFlow */
    private $approvalFlow;
    /** @var BusinessMember */
    private $requester;
    /** @var Business */
    private $business;
    /** @var array */
    private $businessMemberIds;
    private $businessMembersOfThisDepartment;
    private $businessMembersOfFlow;

    public function __construct()
    {
        $this->businessMemberIds = [];
    }

    /**
     * @param Business $business
     * @return Approvers
     */
    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param TripRequestApprovalFlow $approvalFlow
     * @return Approvers
     */
    public function setApprovalFlow($approvalFlow)
    {
        $this->approvalFlow = $approvalFlow;
        return $this;
    }

    /**
     * @param BusinessMember $requester
     * @return Approvers
     */
    public function setRequester($requester)
    {
        $this->requester = $requester;
        return $this;
    }

    /**
     * @return array
     */
    public function getBusinessMemberIds()
    {
        $this->setBusinessMembersOfThisDepartment();
        $this->setBusinessMembersOfFlow();
        foreach ($this->businessMembersOfFlow as $business_member) {
            if ($this->isRequesterIsInTheApprovalFlow($business_member)) continue;
            elseif (!$this->isMemberOfOtherDepartment($business_member)) $this->pushToMemberId($business_member->id);
            elseif (!$business_member->manager_id) $this->pushToMemberId($business_member->id);
            elseif ($this->isMemberBeneathRequester($business_member)) $this->pushToMemberId($business_member->id);
        }
        return $this->businessMemberIds;
    }

    private function setBusinessMembersOfThisDepartment()
    {
        $this->businessMembersOfThisDepartment = BusinessMember::where('business_id', $this->business->id)->whereHas('role', function ($q) {
            $q->where('business_roles.business_department_id', $this->approvalFlow->business_department_id);
        })->select('id', 'manager_id')->get();
        return $this;
    }

    private function setBusinessMembersOfFlow()
    {
        $this->businessMembersOfFlow = $this->approvalFlow->approvers()->select('id', 'manager_id')->where('business_member.id', '<>', $this->requester->id)->get();
        return $this;
    }

    private function isRequesterIsInTheApprovalFlow(BusinessMember $business_member)
    {
        return $business_member->id == $this->requester->id ? 1 : 0;
    }

    private function isMemberOfOtherDepartment(BusinessMember $business_member)
    {
        return $this->businessMembersOfThisDepartment->where('id', $business_member->id)->first() ? 0 : 1;
    }

    /**
     * @param integer $id
     */
    private function pushToMemberId($id)
    {
        array_push($this->businessMemberIds, $id);
    }

    private function isMemberBeneathRequester(BusinessMember $business_member)
    {
        if ($business_member->manager_id == $this->requester->manager_id) return 0;
        return $this->isRequesterIsManagerOfMember($business_member);
    }

    private function isRequesterIsManagerOfMember(BusinessMember $business_member)
    {
        while ($business_member->manager_id != $this->requester->id) {
            $business_member = $this->businessMembersOfThisDepartment->where('id', $business_member->manager_id)->first();
            if ($business_member->manager_id == null) return 0;
        }
        return 1;

    }
}