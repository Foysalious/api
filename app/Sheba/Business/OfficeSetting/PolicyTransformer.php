<?php namespace App\Sheba\Business\OfficeSetting;

use League\Fractal\TransformerAbstract;

class PolicyTransformer extends TransformerAbstract
{
    public function transform($grace_policy)
    {
        return [
            'id' => $grace_policy->id,
            'from_days' => $grace_policy->from_days,
            'to_days' => $grace_policy->to_days,
            'action_name' => $grace_policy->action,
            'penalty_type' => $grace_policy->penalty_type,
            'penalty_amount' => $grace_policy->penalty_amount
        ];
    }

}