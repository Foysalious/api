<?php

namespace Database\Factories;

use Sheba\Dal\ApprovalSettingApprover\ApprovalSettingApprover;

class ApprovalSettingApproverFactory extends Factory
{
    protected $model = ApprovalSettingApprover::class;

    public function definition()
    {
        return array_merge($this->commonSeeds, [
            'type' => 'employee',
            'order' => '1',
        ]);
    }
}