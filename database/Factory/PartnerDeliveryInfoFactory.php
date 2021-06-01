<?php


namespace Factory;


use FontLib\Table\Type\name;
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
            'district'=> 'Dhaka',
            'thana'=>'Gulshan',
            'account_type'=>'bank',
            'account_holder_name' => $this->faker->name,
            'bank_name'=>'Brac BANK',
            'branch_name'=>'Gulshan',
            'account_number'=>'2341000886765001',
            'routing_number'=>'2460001',
            'delivery_vendor'=>'Paperfly'
        ]);
    }
}