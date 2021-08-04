<?php namespace App\Sheba\Business\OfficeSetting;

use League\Fractal\TransformerAbstract;
use Sheba\Dal\OfficePolicyRule\ActionType;

class PolicyTransformer extends TransformerAbstract
{
    public function transform($policy)
    {
        return [
            'id' => $policy->id,
            'from_days' => $policy->from_days,
            'to_days' => $policy->to_days,
            'action_name' => $policy->action,
            'leave_penalty_type' => $policy->action == ActionType::LEAVE_ADJUSTMENT ? (int)$policy->penalty_type : null,
            'salary_penalty_type' => $policy->action == ActionType::SALARY_ADJUSTMENT ? $policy->penalty_type : null,
            'cash_penalty_amount' => $policy->action == ActionType::CASH_PENALTY ? floatval($policy->penalty_amount) : null,
            'leave_penalty_amount' => $policy->action == ActionType::LEAVE_ADJUSTMENT ? floatval($policy->penalty_amount) : null,
            'salary_penalty_amount' => $policy->action == ActionType::SALARY_ADJUSTMENT ? floatval($policy->penalty_amount) : null,
        ];
    }

}