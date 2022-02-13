<?php namespace App\Sheba\Business\PolicyHistory\Policies;

use Sheba\Dal\LateCheckEarlyOutPolicyHistory\LateCheckEarlyOutPolicyHistoryRepository;
use App\Sheba\Business\PolicyHistory\PolicyHistory;
use Carbon\Carbon;

class LateCheckinCheckoutPolicyHistory extends PolicyHistory
{
    /** @var LateCheckEarlyOutPolicyHistoryRepository $lateCheckEarlyOutPolicyHistoryRepo */
    private $lateCheckEarlyOutPolicyHistoryRepo;
    private $policyHistory;

    public function __construct()
    {
        $this->lateCheckEarlyOutPolicyHistoryRepo = app(LateCheckEarlyOutPolicyHistoryRepository::class);
    }

    public function setPolicyHistory(PolicyHistory $policy_history)
    {
        $this->policyHistory = $policy_history;
        return $this;
    }

    public function createHistory()
    {
        $this->lateCheckEarlyOutPolicyHistoryRepo->create([
            'business_id' => $this->policyHistory->business->id,
            'is_enable' => $this->policyHistory->requestData->is_checkin_checkout_policy_enable,
            'settings' => $this->policyHistory->requestData->is_checkin_checkout_policy_enable ?
                $this->policyHistory->requestData->checkin_checkout_policy_rules :
                json_encode([]),
            'start_date' => Carbon::now(),
            'for_checkin' => $this->policyHistory->requestData->for_checkin,
            'for_checkout' => $this->policyHistory->requestData->for_checkout
        ]);
    }
}