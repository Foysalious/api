<?php

namespace Database\Factories;

use Sheba\Dal\PayrollSetting\PayrollSetting;

class PayrollSettingsFactory extends Factory
{
    protected $model = PayrollSetting::class;

    public function definition(): array
    {
        return [
            'is_enable'       => '1',
            'created_by'      => '1',
            'created_by_name' => 'Nawshin Tabassum',
            'updated_by'      => '1',
            'updated_by_name' => 'Nawshin Tabassum',
        ];
    }
}
