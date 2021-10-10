<?php namespace Factory;

use App\Models\TopUpVendor;

class TopUpVendorFactory extends Factory
{
    protected function getModelClass()
    {
        return TopUpVendor::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'name' => 'Mock',
            'amount' => '100000',
            'gateway' => 'ssl',
            'sheba_commission' => 4.0,
            'is_published' => 1,
        ]);
    }
}
