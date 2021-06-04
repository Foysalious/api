<?php namespace App\Sheba\Business\OfficeSetting;

use App\Models\Business;
use Sheba\Dal\OfficePolicy\Type;
use Sheba\Dal\OfficePolicyRule\ActionType;

class PolicyRuleRequester
{
    private $business;
    private $policy;
    private $rules;
    private $policyType;
    private $isEnable;
    private $forLateCheckIn;
    private $forEarlyCheckOut;
    private $deleteRules;
    private $errorMessage;

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

    public function setRules($rules)
    {
        $rules = json_decode($rules, 1);
        $this->rules = $rules;
        $this->checkValidation();
        return $this;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function getPolicy()
    {
        if ($this->policyType == Type::GRACE_PERIOD) $this->policy = $this->business->gracePolicy;
        if ($this->policyType == Type::UNPAID_LEAVE) $this->policy = $this->business->unpaidLeavePolicy;
        if ($this->policyType == Type::LATE_CHECKIN_EARLY_CHECKOUT) $this->policy = $this->business->checkinCheckoutPolicy;
        return $this->rules ? $this->policy : null;
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

    private function checkValidation()
    {
        $from_days = [];
        $to_days = [];
        foreach ($this->rules as $rule) {
            $from_days[] = $rule['from'];
            $to_days[] = $rule['to'];
            if ($rule['from'] < $rule['to']) return $this->errorMessage = 'From days cannot be smaller than To days';
            if ($rule['action'] == ActionType::CASH_PENALTY && empty($rule['penalty_amount'])) return $this->errorMessage = 'Penalty Amount Not Set';
            if ($rule['action'] == ActionType::LEAVE_ADJUSTMENT){
                if (empty($rule['penalty_type'])) return $this->errorMessage = 'Please select, on what penalty should be applied.';
                if (empty($rule['penalty_amount'])) return $this->errorMessage = 'Please select the days';
            }
        }
        $unique_from_days = array_unique($from_days);
        $duplicate_from_days = array_diff_assoc($from_days, $unique_from_days);
        $unique_to_days = array_unique($to_days);
        $duplicate_to_days = array_diff_assoc($to_days, $unique_to_days);
        $overlapping = array_intersect($from_days,$to_days);
        if ($duplicate_from_days || $duplicate_to_days || $overlapping) return $this->errorMessage = 'Duplicate Rules or Days are Overlapping. Please Check again.';
    }

    public function getError()
    {
        return $this->errorMessage;
    }

}