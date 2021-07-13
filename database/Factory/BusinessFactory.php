<?php namespace Factory;


use App\Models\Business;

class BusinessFactory extends Factory
{

    protected function getModelClass()
    {
        return Business::class;// TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'name' =>'My Company',
            'sub_domain'=>'my-company',
            'type'=>'Company',
            'is_verified'=> 1
        ]);
    }
}