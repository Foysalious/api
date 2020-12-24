<?php namespace Sheba\Business\TripRequestApproval;

use Sheba\Dal\TripRequestApproval\TripRequestApprovalRepositoryInterface;
use Sheba\Dal\TripRequestApproval\Model as TripRequestApproval;
use Illuminate\Support\Facades\DB;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;

    private $tripRequestApprovalRepo;
    private $tripRequestApproval;
    private $member;
    private $businessMember;
    private $statuses;
    private $data;

    public function __construct(TripRequestApprovalRepositoryInterface $trip_request_approval_repo)
    {
        $this->tripRequestApprovalRepo = $trip_request_approval_repo;
        $this->statuses = config('b2b.TRIP_REQUEST_APPROVAL_STATUS');
    }

    public function hasError()
    {
        if (!in_array($this->data['status'], $this->statuses)) return "Invalid Status!";
        if ($this->tripRequestApproval->business_member_id != $this->businessMember->id) return "You are not authorized to  change the Status!";
        return false;
    }

    public function setMember($member)
    {
        $this->member = $member;
        return $this;
    }

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setTripRequestApproval($approval)
    {
        $this->tripRequestApproval = TripRequestApproval::findOrFail($approval);
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function change()
    {
        $this->setModifier($this->member);
        $data = ['status' => $this->data['status']];
        $this->tripRequestApprovalRepo->update($this->tripRequestApproval, $this->withUpdateModificationField($data));
    }
}
