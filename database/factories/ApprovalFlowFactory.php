<?php

namespace Database\Factories;

use Sheba\Dal\ApprovalFlow\Model as ApprovalFlow;

class ApprovalFlowFactory extends Factory
{
    protected $model = ApprovalFlow::class;

    public function definition()
    {
        return array_merge($this->commonSeeds, [
            'title' => 'Test flow',
            'type' => 'leave',
            'business_department_id' => 1,
        ]);
    }

}