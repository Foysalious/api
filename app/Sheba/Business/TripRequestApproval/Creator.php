<?php namespace Sheba\Business\TripRequestApproval;

use App\Models\BusinessMember;
use App\Models\BusinessTripRequest;
use Sheba\Dal\ApprovalFlow\Type as Type;
use Sheba\Dal\TripRequestApproval\EloquentImplementation;

class Creator
{
    /** @var BusinessTripRequest */
    private $tripRequest;
    /** @var Approvers */
    private $approvers;
    /** @var EloquentImplementation */
    private $tripRequestApprovalRepository;
    /** @var array $approvers_id*/
    private $approvers_id;

    public function __construct(Approvers $approvers, EloquentImplementation $eloquent_implementation)
    {
        $this->approvers = $approvers;
        $this->tripRequestApprovalRepository = $eloquent_implementation;
    }

    /**
     * @param BusinessTripRequest $tripRequest
     * @return Creator
     */
    public function setTripRequest($tripRequest)
    {
        $this->tripRequest = $tripRequest;
        $this->approvers_id = $this->setBusinessMember();

        return $this;
    }

    private function setBusinessMember()
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->tripRequest->getBusinessMember();
        $approval_flow_by = $business_member->role->businessDepartment->approvalFlowBy(Type::TRIP);
        if (!$business_member->role || !$business_member->role->businessDepartment || !$approval_flow_by)
            return [];

        return $this->approvers
            ->setApprovalFlow($approval_flow_by)
            ->setBusiness($business_member->business)
            ->setRequester($business_member)
            ->getBusinessMemberIds();
    }

    public function create()
    {
        foreach ($this->approvers as $approver) {
            $this->tripRequestApprovalRepository->create([
                'business_trip_request_id' => $this->tripRequest->id,
                'business_member_id' => $approver
            ]);
        }
    }
}
