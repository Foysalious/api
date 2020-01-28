<?php namespace Sheba\TripRequestApproval;


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

    private function pushToMemberId(array $element)
    {
        $this->businessMemberIds = array_merge($this->businessMemberIds, $element);
    }

    /**
     * @return array
     */
    public function getBusinessMemberIds()
    {
        $this->setBusinessMembersOfThisDepartment();
        $this->setBusinessMembersOfFlow();
        $business_member_ids_of_this_department = $this->businessMembersOfThisDepartment->pluck('id')->toArray();
        foreach ($this->businessMembersOfFlow as $business_member) {
            if ($business_member->manager_id == null || !in_array($business_member->id, $business_member_ids_of_this_department)) {
                $this->pushToMemberId([$business_member->id]);
                continue;
            } else {
                $this->canSentApproval($business_member);
            }
        }
        dd($this->businessMemberIds);
    }

    private function canSentApproval(BusinessMember $business_member)
    {
        if ($business_member->manager_id == $this->requester->manager_id) return false;
        while($business_member->manager_id==)
    }
}