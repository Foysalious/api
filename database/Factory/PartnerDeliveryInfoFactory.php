<?php


namespace Factory;


use Sheba\Dal\PartnerDeliveryInformation\Model;

class PartnerDeliveryInfoFactory extends Factory
{
    protected function getModelClass()
    {
        return Model::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds,[
            'partner_id' => 1,
            'name'=>'$this->faker->name',
            'mobile'=>'+8801678242967',
            'email'=>'test@gmail.com',
            'business_type'=>"Construction",
        ]);
    }
}