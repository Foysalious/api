<?php

namespace Database\Factories;

use App\Models\BusinessDepartment;

class BusinessDepartmentFactory extends Factory
{
    protected $model = BusinessDepartment::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'name'         => 'IT',
            'is_published' => 1,
        ]);
    }
}
