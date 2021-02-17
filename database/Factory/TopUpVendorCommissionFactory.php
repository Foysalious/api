<?php namespace Factory;


use App\Models\TopUpVendorCommission;

class TopUpVendorCommissionFactory extends Factory
{
    protected function getModelClass()
    {
       return TopUpVendorCommission::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'agent_commission' => '1.00',
            'ambassador_commission' => '0.20',
            'type' =>'App\Models\Affiliate',
        ]);
    }
}
