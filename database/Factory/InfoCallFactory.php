<?php namespace Factory;

use Sheba\Dal\InfoCall\InfoCall;

class InfoCallFactory extends Factory
{
    protected function getModelClass()
    {
        return InfoCall::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'customer_mobile' => '01620011019',
            'location_id' => '4',
            'priority' => 'High',
            'flag' => 'Red',
            'status' => 'Open',
            //'service_id'=> '1',
            'service_name'=> 'Ac service'
            //  'created_at'=>
        ]);
    }
}