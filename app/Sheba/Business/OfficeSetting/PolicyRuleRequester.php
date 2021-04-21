<?php namespace App\Sheba\Business\OfficeSetting;

use App\Models\Business;
use Sheba\Dal\OfficePolicy\Type;

class PolicyRuleRequester
{
    private $business;
    private $policy;
    private $rules;
    private $policyType;

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function getBusiness()
    {
        return $this->business;
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

}