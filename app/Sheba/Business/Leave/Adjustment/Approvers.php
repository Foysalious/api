<?php namespace Sheba\Business\Leave\Adjustment;

use App\Models\Business;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class Approvers
{
    private $business;
    private $business_member_repo;

    /**
     * Approvers constructor.
     * @param BusinessMemberRepositoryInterface $business_member_repository
     */
    public function __construct(BusinessMemberRepositoryInterface $business_member_repository)
    {
        $this->business_member_repo = $business_member_repository;
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;

    }

    /**
     * @return array
     */
    public function getApprovers()
    {
        $approvers = $this->business_member_repo->where('business_id', $this->business->id)->where('is_super', 1)->get();
        $super_admins = [];
        $approvers->each(function ($approver) use (&$super_admins) {

            $member = $approver->member;
            $profile = $member->profile;
            array_push($super_admins, [
                'id' => $approver->id,
                'name' => $profile->name
            ]);
        });
        return $super_admins;
    }

}