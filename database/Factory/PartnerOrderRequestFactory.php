<?php namespace Factory;


use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;

class PartnerOrderRequestFactory extends Factory
{

    protected function getModelClass()
    {
        return PartnerOrderRequest::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'status' => 'pending'
        ]);
    }
}
