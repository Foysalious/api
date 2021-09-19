<?php namespace App\Sheba\Business\OfficeSetting;

use League\Fractal\TransformerAbstract;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Sheba\Dal\OfficePolicyRule\ActionType;
use Sheba\Dal\PayrollComponent\PayrollComponent;

class PolicyTransformer extends TransformerAbstract
{
    public function transform($policy)
    {
        return [
            'id' => $policy->id,
            'from_days' => $policy->from_days,
            'to_days' => $policy->to_days,
            'action_name' => $policy->action,
            'action_title' => ucwords(str_replace('_', ' ', $policy->action)),
            'leave_penalty_type' => $policy->action == ActionType::LEAVE_ADJUSTMENT ? (int)$policy->penalty_type : null,
            'leave_penalty_name' => $policy->action == ActionType::LEAVE_ADJUSTMENT ? $this->getLeavePenaltyName((int)$policy->penalty_type) : null,
            'salary_penalty_type' => $policy->action == ActionType::SALARY_ADJUSTMENT ? $policy->penalty_type : null,
            'salary_penalty_name' => $policy->action == ActionType::SALARY_ADJUSTMENT ? $this->getSalaryPenaltyName($policy->penalty_type) : null,
            'cash_penalty_amount' => $policy->action == ActionType::CASH_PENALTY ? floatval($policy->penalty_amount) : null,
            'leave_penalty_amount' => $policy->action == ActionType::LEAVE_ADJUSTMENT ? floatval($policy->penalty_amount) : null,
            'salary_penalty_amount' => $policy->action == ActionType::SALARY_ADJUSTMENT ? floatval($policy->penalty_amount) : null,
        ];
    }

    private function getLeavePenaltyName($leave_type_id)
    {
        $leave_type = LeaveType::withTrashed()->findOrFail($leave_type_id);
        return $leave_type->title;
    }

    private function getSalaryPenaltyName($component)
    {
        return ucwords(str_replace('_', ' ', $component));
    }

}