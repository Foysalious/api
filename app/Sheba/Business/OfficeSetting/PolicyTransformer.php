<?php namespace App\Sheba\Business\OfficeSetting;

use League\Fractal\TransformerAbstract;

class PolicyTransformer extends TransformerAbstract
{
    public function transform($policy)
    {
        return [
            'id' => $policy->id,
            'from_days' => $policy->from_days,
            'to_days' => $policy->to_days,
            'action_name' => $policy->action,
            'penalty_type' => $policy->penalty_type,
            'penalty_amount' => $policy->penalty_amount
        ];
    }

}