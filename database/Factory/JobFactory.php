<?php namespace Factory;


use App\Models\Job;

class JobFactory extends Factory
{

    protected function getModelClass()
    {
        return Job::class;// TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'id' => 1,
            'job_name' => 'Pest Control Package Service',
            'service_name' => 'Pest Control Package Service',
            'service_quantity' => 1,
            'needs_logistic' => 0,
            'logistic_enabled_manually' => 0,
            'service_unit_price' => '2400.00',
            'material_commission_rate' => '15.00',
            'site' => 'customer',
            'delivery_charge' => '0.00'
     ]);// TODO: Implement getData() method.
    }
}