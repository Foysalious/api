<?php

namespace Database\Factories;

use App\Models\Resource;

class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        return array_merge($this->commonSeeds, [
            'father_name'    => $this->faker->name,
            'remember_token' => randomString(60, 1, 1),
            'status'         => 'Verified',
            'is_verified'    => 1,
            'wallet'         => '10000',
            'reward_point'   => '0',
        ]);
    }
}
