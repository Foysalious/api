<?php namespace Sheba\Business\TripRequestApproval;

use App\Models\Business;
use App\Models\BusinessMember;
use Sheba\Dal\ApprovalFlow\Model as ApprovalFlow;

class Approvers
{
    /** @var ApprovalFlow $approvalFlow */
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
     * @param ApprovalFlow $approvalFlow
     * @return $this
     */
    public function setApprovalFlow(ApprovalFlow $approvalFlow)
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
            if ($this->isBusinessMemberIsTheRequester($business_member) || $this->isBusinessMemberAndRequesterHasSameManager($business_member)) continue;
            elseif ($this->isBusinessMemberFromOtherDepartment($business_member)) $this->pushToMemberId($business_member->id);
            elseif (!$business_member->manager_id) $this->pushToMemberId($business_member->id);
            elseif (!$this->isRequesterAboveBusinessMember($business_member)) $this->pushToMemberId($business_member->id);
        }

        return $this->businessMemberIds;
    }

    /**
     * Business Members of this Department
     *
     * @return $this
     */
    private function setBusinessMembersOfThisDepartment()
    {
        $this->businessMembersOfThisDepartment = BusinessMember::where('business_id', $this->business->id)->whereHas('role', function ($q) {
            $q->where('business_roles.business_department_id', $this->approvalFlow->business_department_id);
        })->select('id', 'manager_id')->get();

        return $this;
    }

    private function setBusinessMembersOfFlow()
    {
        $this->businessMembersOfFlow = $this->approvalFlow->approvers()->select('id', 'manager_id')
            ->where('business_member.id', '<>', $this->requester->id)
            ->get();

        return $this;
    }

    private function isBusinessMemberIsTheRequester(BusinessMember $business_member)
    {
        return $business_member->id == $this->requester->id;
    }

    /**
     * @param BusinessMember $business_member
     * @return bool
     */
    private function isBusinessMemberAndRequesterHasSameManager(BusinessMember $business_member)
    {
        return $business_member->manager_id == $this->requester->manager_id;
    }

    /**
     * @param BusinessMember $business_member
     * @return int
     */
    private function isBusinessMemberFromOtherDepartment(BusinessMember $business_member)
    {
        return $this->businessMembersOfThisDepartment->where('id', $business_member->id)->first() ? 0 : 1;
    }

    /**
     * @param $id
     */
    private function pushToMemberId($id)
    {
        array_push($this->businessMemberIds, $id);
    }

    /**
     * @param BusinessMember $business_member
     * @return int
     */
    private function isRequesterAboveBusinessMember(BusinessMember $business_member)
    {
        if ($this->requester->manager_id == null) return 1;
        if ($this->isBusinessMemberManagerOfRequester($business_member)) return 0;
        return $this->isRequesterIsManagerOfMember($business_member);
    }

    /**
     * @param BusinessMember $business_member
     * @return bool
     */
    private function isBusinessMemberManagerOfRequester(BusinessMember $business_member)
    {
        return $this->requester->manager_id == $business_member->id;
    }

    /**
     * @param BusinessMember $business_member
     * @return int
     */
    private function isRequesterIsManagerOfMember(BusinessMember $business_member)
    {
        while ($business_member->manager_id != $this->requester->id) {
            $business_member = $this->businessMembersOfThisDepartment->where('id', $business_member->manager_id)->first();
            if (!$business_member || $business_member->manager_id == null) return 0;
        }

        return 1;
    }
}
