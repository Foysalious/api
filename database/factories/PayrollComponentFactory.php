<?php

namespace Database\Factories;

use Sheba\Dal\PayrollComponent\PayrollComponent;

class PayrollComponentFactory extends Factory
{
    protected $model = PayrollComponent::class;

    public function definition(): array
    {
        return [
            'created_by'      => '1',
            'created_by_name' => 'Nawshin Tabassum',
            'updated_by'      => '1',
            'updated_by_name' => 'Nawshin Tabassum',
        ];
    }
}
