<?php


namespace Factory;


use App\Models\Partner;

class PartnerFactory extends Factory
{

    protected function getModelClass()
    {
        return Partner::class; // TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'name'=> $this->faker->name,
            'package_id'=>2,
            'mobile' => '+8801666777555',
            'password' => bcrypt(89079),
            'status' => 'Verified',
            'wallet' => 50000
        ]);// TODO: Implement getData() method.
    }
}