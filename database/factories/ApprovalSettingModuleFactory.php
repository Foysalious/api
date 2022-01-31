<?php

namespace Database\Factories;

use Sheba\Dal\ApprovalSettingModule\ApprovalSettingModule;

class ApprovalSettingModuleFactory extends Factory
{
    protected $model = ApprovalSettingModule::class;

    public function definition()
    {
        return array_merge($this->commonSeeds, [
            'modules' => 'leave',
        ]);
    }
}