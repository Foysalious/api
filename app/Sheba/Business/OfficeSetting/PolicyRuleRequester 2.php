<?php namespace App\Sheba\Business\OfficeSetting;

use App\Models\Business;
use Sheba\Dal\OfficePolicy\Type;

class PolicyRuleRequester
{
    private $business;
    private $policy;
    private $rules;
    private $policyType;
    private $isEnable;
    private $forLateCheckIn;
    private $forEarlyCheckOut;

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function getBusiness()
    {
        return $this->business;
    }

    public function setIsEnable($is_enable)
    {
        $this->isEnable = $is_enable;
        return $this;
    }

    public function getIsEnable()
    {
        return $this->isEnable;
    }

    public function setPolicyType($policy_type)
    {
        $this->policyType = $policy_type;
        return $this;
    }

    public function getPolicyType()
    {
        return $this->policyType;
    }

    public function getPolicy()
    {
        if ($this->policyType == Type::GRACE_PERIOD) $this->policy = $this->business->gracePolicy;
        if ($this->policyType == Type::UNPAID_LEAVE) $this->policy = $this->business->unpaidLeavePolicy;
        if ($this->policyType == Type::GRACE_PERIOD) $this->policy = $this->business->checkinCheckoutPolicy;
        return $this->policy;
    }

    public function setRules($rules)
    {
        $rules = json_decode($rules, 1);
        $this->rules = $rules;
        return $this;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function setForLateCheckIn($for_late_checkin)
    {
        $this->forLateCheckIn = $for_late_checkin;
        return $this;
    }

    public function getForLateCheckIn()
    {
        return $this->forLateCheckIn;
    }

    public function setForEarlyCheckOut($for_early_checkout)
    {
        $this->forEarlyCheckOut = $for_early_checkout;
        return $this;
    }

    public function getForEarlyCheckOut()
    {
        return $this->forEarlyCheckOut;
    }

}