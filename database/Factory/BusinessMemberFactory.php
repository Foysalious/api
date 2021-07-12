<?php


namespace Factory;


use App\Models\BusinessMember;

class BusinessMemberFactory extends Factory
{

    protected function getModelClass()
    {
        return BusinessMember::class;// TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'member_id'=>1,
            'business_id'=>1,
            'type'=>'Admin',
            'is_verified'=> 1,
            'status'=> 'active',
            'is_super'=> 1
        ]);
    }
}