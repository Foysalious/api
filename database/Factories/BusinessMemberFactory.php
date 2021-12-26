<?php

namespace Database\Factories;

use App\Models\BusinessMember;

class BusinessMemberFactory extends Factory
{
    protected $mode = BusinessMember::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'type'        => 'Admin',
            'is_verified' => 1,
            'status'      => 'active',
            'is_super'    => 1,
        ]);
    }
}
