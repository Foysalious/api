<?php namespace Factory;


use App\Models\Resource;

class ResourceFactory extends Factory
{

    protected function getModelClass()
    {
        return Resource::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'father_name' => $this->faker->name,
            'remember_token' => randomString(60,1,1),
            'status' => 'Verified',
            'is_verified' => 1,
            'wallet' => '10000',
            'reward_point' => '0',
        ]);
    }
}
