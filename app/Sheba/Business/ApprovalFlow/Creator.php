<?php namespace Sheba\Business\ApprovalFlow;

class Creator
{
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

    public function setSuperAdmins($super_admins)
    {
        $this->superAdmins = $super_admins;
        return $this;
    }
}
