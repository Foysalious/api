<?php namespace Factory;


use App\Models\Member;

class MemberFactory extends Factory
{
    protected function getModelClass()
    {
        return Member::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'profile_id' => 1,
            'remember_token' => $this->faker->randomLetter,
             'is_verified' => 1,
        ]);
    }
}
