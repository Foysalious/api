<?php

namespace Database\Factories;

use App\Models\Member;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'remember_token' => $this->faker->randomNumber(5),
            'is_verified'    => 1,
        ]);
    }
}
