<?php namespace App\Sheba\Business\PolicyHistory;

use App\Sheba\Business\PolicyHistory\Policies\LateCheckinCheckoutPolicyHistory;
use App\Sheba\Business\PolicyHistory\Policies\GracePolicyHistory;
use App\Sheba\Business\PolicyHistory\Policies\LeavePolicyHistory;
use Illuminate\Http\Request;
use App\Models\Business;

class PolicyHistory
{
    protected $requestData;
    protected $business;
    private $isForUnpaidLeavePolicy;

    public function setRequest(Request $request)
    {
        $this->requestData = $request;
        return $this;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function isForUnpaidLeavePolicy($is_unpaid_leave_policy = 0)
    {
        $this->isForUnpaidLeavePolicy = $is_unpaid_leave_policy;
        return $this;
    }

    public function createPolicies()
    {
        if (!$this->isForUnpaidLeavePolicy) (new GracePolicyHistory())->setPolicyHistory($this)->createHistory();
        if (!$this->isForUnpaidLeavePolicy) (new LateCheckinCheckoutPolicyHistory())->setPolicyHistory($this)->createHistory();
        if ($this->isForUnpaidLeavePolicy) (new LeavePolicyHistory())->setPolicyHistory($this)->createHistory();
    }
}