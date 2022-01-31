<?php

namespace Database\Factories;

use App\Models\Department;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;
    public function definition()
    {
        // TODO: Implement definition() method.
        return array_merge($this->commonSeeds, [
            'name'    => 'IT',
        ]);
    }
}