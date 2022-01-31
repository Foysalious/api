<?php

namespace Database\Factories;

use App\Models\BusinessRole;

class BusinessRoleFactory extends Factory
{
    protected $model = BusinessRole::class;

    public function definition()
    {
        // TODO: Implement definition() method.
        return array_merge($this->commonSeeds, [
            'name'                      => 'Manager',
            'is_published'              => 1
        ]);

    }
}