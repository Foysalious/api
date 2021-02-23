<?php namespace Sheba\Business\BusinessMember\Events;

use App\Models\BusinessMember;

class BusinessMemberCreated
{
    public $businessMember;

    /**
     * Create a new event instance.
     *
     * @param BusinessMember $business_member
     */
    public function __construct(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
    }
}