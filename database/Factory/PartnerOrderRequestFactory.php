<?php namespace Factory;


use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;

class PartnerOrderRequestFactory extends Factory
{

    protected function getModelClass()
    {
        return PartnerOrderRequest::class;// TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'id' => 2,
            'status' => 'pending',
            'created_by' => 1,
            'created_by_name' => 233,
            'updated_by' => 0,
            'updated_by_name' => null
        ]);// TODO: Implement getData() method.
    }
}