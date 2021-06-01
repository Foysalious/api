<?php namespace Factory;


use App\Models\Profile;

class ProfileFactory extends Factory
{

    protected function getModelClass()
    {
       return Profile::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'name' => $this->faker->name,
            'mobile' =>'+8801678242955',
            'email' =>'tisha@sheba.xyz',
            'password' =>bcrypt('12345'),
            'remember_token' => $this->faker->randomLetter,
            'is_blacklisted'=> 0,
            'mobile_verified'=>1,
            'email_verified'=>1,
            'address'=>$this->faker->address,
            'nid_verification_request_count'=>0,
            'blood_group'=>'B+'
        ]);
    }
}
