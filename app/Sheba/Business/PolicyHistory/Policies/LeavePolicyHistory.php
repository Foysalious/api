<?php namespace App\Sheba\Business\PolicyHistory\Policies;

use Carbon\Carbon;
use Sheba\Dal\UnpaidLeavePolicyHistory\UnpaidLeavePolicyHistoryRepository;
use App\Sheba\Business\PolicyHistory\PolicyHistory;

class LeavePolicyHistory extends PolicyHistory
{
    /**  @var UnpaidLeavePolicyHistoryRepository $unpaidLeavePolicyHistoryRepo */
    private $unpaidLeavePolicyHistoryRepo;
    private $policyHistory;

    public function __construct()
    {
        $this->unpaidLeavePolicyHistoryRepo = app(UnpaidLeavePolicyHistoryRepository::class);
    }

    public function setPolicyHistory(PolicyHistory $policy_history)
    {
        $this->policyHistory = $policy_history;
        return $this;
    }

    public function createHistory()
    {
        $this->unpaidLeavePolicyHistoryRepo->create([
            'business_id' => $this->policyHistory->business->id,
            'is_enable' => $this->policyHistory->requestData->is_enable,
            'settings' => $this->policyHistory->requestData->is_enable ?
                $this->policyHistory->requestData->rules :
                json_encode(['component' => $this->policyHistory->requestData->component]),
            'start_date' => Carbon::now()
        ]);
    }
}