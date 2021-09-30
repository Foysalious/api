<?php namespace Sheba\Business\BusinessMemberStatusChangeLog;

use Sheba\Dal\BusinessMemberStatusChangeLog\Contract as BusinessMemberStatusChangeLogRepo;
use App\Models\BusinessMember;
use App\Models\Profile;
use App\Models\Member;

class Creator
{
    /** @var  BusinessMemberStatusChangeLogRepo $business_member_status_change_log_repo */
    private $businessMemberStatusChangeLogRepo;

    /** @var BusinessMember $businessMember */
    private $businessMember;
    /** @var Member $member */
    private $member;
    /** @var Profile $profile */
    private $profile;

    private $fromStatus;
    private $toStatus;

    public function __construct(BusinessMemberStatusChangeLogRepo $business_member_status_change_log_repo)
    {
        $this->businessMemberStatusChangeLogRepo = $business_member_status_change_log_repo;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->member = $business_member->member;
        $this->profile = $this->member->profile;
        return $this;
    }

    public function setFromStatus($from_status)
    {
        $this->fromStatus = $from_status;
        return $this;
    }

    public function setToStatus($to_status)
    {
        $this->toStatus = $to_status;
        return $this;
    }

    public function create()
    {
        $data = [
            'business_member_id' => $this->businessMember->id,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'log' => $this->getLog()
        ];

        return $this->businessMemberStatusChangeLogRepo->create($data);
    }

    private function getLog()
    {
        return "Super Admin invited " . $this->getName() . " again";
    }

    private function getName()
    {
        if ($this->profile->name == " " || !$this->profile->name) return 'n/s';
        return $this->profile->name;
    }
}