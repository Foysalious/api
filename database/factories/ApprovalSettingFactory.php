<?php

namespace Database\Factories;

use Sheba\Dal\ApprovalSetting\ApprovalSetting;

class ApprovalSettingFactory extends Factory
{
    protected $model = ApprovalSetting::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'target_type' => 'global',
            'note'        => 'Default Approval Setting',
        ]);
    }
}
