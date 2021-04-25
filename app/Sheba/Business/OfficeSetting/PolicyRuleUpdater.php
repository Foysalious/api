<?php namespace App\Sheba\Business\OfficeSetting;

use Illuminate\Support\Facades\DB;
use Sheba\Dal\OfficePolicyRule\OfficePolicyRuleRepository;

class PolicyRuleUpdater
{
    /*** @var PolicyRuleRequester */
    private $policyRuleRequester;
    /*** @var OfficePolicyRuleRepository */
    private $officePolicyRuleRepository;
    private $rulesData = [];

    public function __construct()
    {
        $this->officePolicyRuleRepository = app(OfficePolicyRuleRepository::class);
    }

    public function setPolicyRuleRequester(PolicyRuleRequester $policy_rule_requester)
    {
        $this->policyRuleRequester = $policy_rule_requester;
        return $this;
    }

    public function update()
    {
        $previous_policy = $this->policyRuleRequester->getPolicy();
        $this->makeData();
        DB::transaction(function () use ($previous_policy){
            if ($previous_policy) $this->deletePreviousPolicy($previous_policy);
            $this->officePolicyRuleRepository->insert($this->rulesData);
        });
    }

    private function makeData()
    {
        $business = $this->policyRuleRequester->getBusiness();
        $policy_type = $this->policyRuleRequester->getPolicyType();
        foreach ($this->policyRuleRequester->getRules() as $rules) {
            array_push($this->rulesData, [
                'business_id' => $business->id,
                'policy_type' => $policy_type,
                'from_days' => $rules['from'],
                'to_days' => $rules['to'],
                'action' => $rules['action'],
                'penalty_type' => $rules['penalty_type'],
                'penalty_amount' => $rules['penalty_amount']
            ]);
        }
    }

    private function deletePreviousPolicy($previous_policy)
    {
        foreach ($previous_policy as $policy) {
            $this->officePolicyRuleRepository->delete($policy);
        }
    }
}