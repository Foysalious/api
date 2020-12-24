<?php namespace App\Sheba\Business;

use App\Models\Business;
use App\Models\Member;

abstract class BusinessCommonInformation
{

    /** @var Business $business */
    protected $business;
    /** @var Member $member */
    protected $member;
    
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
     * @param Member $member
     * @return $this
     */
    public function setMember(Member $member)
    {
        $this->member = $member;
        return $this;
    }

    public abstract function create();
}