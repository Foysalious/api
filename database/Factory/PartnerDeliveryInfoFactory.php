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
            'name'=>'Only for You',
            'mobile'=>'01456456456',
            'email'=>'only4u@gmail.com',
            'business_type'=>"Construction",
        ]);
    }
}