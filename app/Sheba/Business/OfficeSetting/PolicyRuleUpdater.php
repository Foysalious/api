<?php namespace App\Sheba\Business\OfficeSetting;

use Illuminate\Support\Facades\DB;
use Sheba\Dal\BusinessOfficeHours\Contract as BusinessOfficeHours;
use Sheba\Dal\OfficePolicy\Type;
use Sheba\Dal\OfficePolicyRule\OfficePolicyRuleRepository;

class PolicyRuleUpdater
{
    /*** @var PolicyRuleRequester */
    private $policyRuleRequester;
    /*** @var OfficePolicyRuleRepository */
    private $officePolicyRuleRepository;
    private $rulesData = [];
    private $businessOfficeHourRepoository;
    private $business;
    private $policyType;

    public function __construct()
    {
        $this->officePolicyRuleRepository = app(OfficePolicyRuleRepository::class);
        $this->businessOfficeHourRepoository = app(BusinessOfficeHours::class);
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
            $this->deleteRules();
            $office_hour_data = $this->makeOfficeHourData();
            if ($office_hour_data) $this->businessOfficeHourRepoository->update($this->business->officeHour, $office_hour_data);
            if ($previous_policy) $this->deletePreviousPolicy($previous_policy);
            $this->officePolicyRuleRepository->insert($this->rulesData);
        });
        return true;
    }

    private function makeData()
    {
        $this->business = $this->policyRuleRequester->getBusiness();
        $this->policyType = $this->policyRuleRequester->getPolicyType();
        $policy_rules = $this->policyRuleRequester->getRules();
        if ($policy_rules) {
            foreach ($policy_rules as $rules) {
                array_push($this->rulesData, [
                    'business_id' => $this->business->id,
                    'policy_type' => $this->policyType,
                    'from_days' => $rules['from'],
                    'to_days' => $rules['to'],
                    'action' => $rules['action'],
                    'penalty_type' => $rules['penalty_type'],
                    'penalty_amount' => $rules['penalty_amount']
                ]);
            }
        }
    }

    private function deletePreviousPolicy($previous_policy)
    {
        foreach ($previous_policy as $policy) {
            $this->officePolicyRuleRepository->delete($policy);
        }
    }

    private function makeOfficeHourData()
    {
        $data = [];
        $is_enable = $this->policyRuleRequester->getIsEnable();
        if ($this->policyType == Type::GRACE_PERIOD) $data ['is_grace_period_policy_enable'] = $is_enable;
        if ($this->policyType == Type::UNPAID_LEAVE) $data ['is_unpaid_leave_policy_enable'] = $is_enable;
        if ($this->policyType == Type::LATE_CHECKIN_EARLY_CHECKOUT) {
            $data ['is_late_checkin_early_checkout_policy_enable'] = $is_enable;
            $data['is_for_late_checkin'] = $this->policyRuleRequester->getForLateCheckIn();
            $data['is_for_early_checkout'] = $this->policyRuleRequester->getForEarlyCheckOut();
        }

        return $data;
    }

    private function deleteRules()
    {
        $delete_rules = $this->policyRuleRequester->getDeleteRules();
        if (empty($delete_rules)) return;
        foreach ($delete_rules as $delete_rule) {
            $existing_rule = $this->officePolicyRuleRepository->find($delete_rule);
            if (!$existing_rule) continue;
            $this->officePolicyRuleRepository->delete($existing_rule);
        }
        return true;
    }
}