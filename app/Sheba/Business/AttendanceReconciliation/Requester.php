<?php namespace App\Sheba\Business\AttendanceReconciliation;

use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class Requester
{
    private $business;
    private $businessMember;
    /*** @var BusinessMemberRepositoryInterface $businessMemberRepo*/
    private $businessMemberRepo;
    private $error;
    private $checkinTime;
    private $checkoutTime;

    public function __construct()
    {
        $this->businessMemberRepo = app(BusinessMemberRepositoryInterface::class);
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setBusinessMember($business_member_id)
    {
        $business_member = $this->businessMemberRepo->find($business_member_id);
        if (!$business_member) $this->error = true;
        $this->businessMember = $business_member;
        return $this;
    }

    public function setCheckinTime($checkin_time)
    {
        $this->checkinTime = $checkin_time;
        return $this;
    }

    public function setCheckoutTime($checkout_time)
    {
        $this->checkoutTime = $checkout_time;
        return $this;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getBusinessMember()
    {
        return $this->businessMember;
    }

    public function getCheckinTime()
    {
        return $this->checkinTime;
    }

    public function getCheckoutTime()
    {
        return $this->checkoutTime;
    }
}