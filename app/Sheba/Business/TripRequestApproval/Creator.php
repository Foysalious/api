<?php namespace Sheba\Business\TripRequestApproval;


use App\Models\BusinessMember;
use App\Models\BusinessTripRequest;
use Sheba\Dal\TripRequestApproval\EloquentImplementation;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class Creator
{
    /** @var BusinessTripRequest */
    private $tripRequest;
    /** @var Approvers */
    private $approvers;
    /** @var EloquentImplementation */
    private $tripRequestApprovalRepository;


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
        return $this;
    }

    public function create()
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->tripRequest->getBusinessMember();
        if (!$business_member->role || !$business_member->role->businessDepartment || !$business_member->role->businessDepartment->tripRequestFlow) return;
        $ids = $this->approvers->setApprovalFlow($business_member->role->businessDepartment->tripRequestFlow)->setBusiness($business_member->business)
            ->setRequester($business_member->member)->getBusinessMemberIds();
        foreach ($ids as $id) {
            $this->tripRequestApprovalRepository->create(['business_trip_request_id' => $this->tripRequest->id, 'business_member_id' => $id]);
        }
    }

}