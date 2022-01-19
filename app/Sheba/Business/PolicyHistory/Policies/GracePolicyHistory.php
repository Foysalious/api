<?php namespace App\Sheba\Business\PolicyHistory\Policies;

use Sheba\Dal\GracePeriodPolicyHistory\GracePeriodPolicyHistoryRepository;
use App\Sheba\Business\PolicyHistory\PolicyHistory;
use Carbon\Carbon;

class GracePolicyHistory extends PolicyHistory
{
    /** @var GracePeriodPolicyHistoryRepository $gracePeriodPolicyHistory */
    private $gracePeriodPolicyHistoryRepo;
    private $policyHistory;

    public function __construct()
    {
        $this->gracePeriodPolicyHistoryRepo = app(GracePeriodPolicyHistoryRepository::class);
    }

    public function setPolicyHistory(PolicyHistory $policy_history)
    {
        $this->policyHistory = $policy_history;
        return $this;
    }

    public function createHistory()
    {
        $this->gracePeriodPolicyHistoryRepo->create([
            'business_id' => $this->policyHistory->business->id,
            'is_enable' => $this->policyHistory->requestData->is_grace_policy_enable,
            'settings' => $this->policyHistory->requestData->is_grace_policy_enable ? $this->policyHistory->requestData->grace_policy_rules : json_encode([]),
            'start_date' => Carbon::now()
        ]);
    }
}