<?php namespace Factory;

use Sheba\Dal\TopUpOTFSettings\Model;

class TopUpOTFSettingsFactory extends Factory
{

    protected function getModelClass()
    {
       return Model::class;
    }

    protected function getData()
    {
             return array_merge($this->commonSeeds, [
                 'applicable_gateways'=> '["ssl","airtel"]',
                 'type'=> 'App\Models\Affiliate',
                 'agent_commission'=> '5.03',
             ]);
    }
}
