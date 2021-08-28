<?php namespace Factory;

use App\Models\District;

class DistrictFactory extends Factory
{
    protected function getModelClass()
    {
        return District::class;
    }

    protected function getData()
    {
        return [
            'division_id' => 1,
            'name' => 'Dhaka',
            'bn_name' => 'ঢাকা',
            'lat' => 23.7115253,
            'lng' => 90.4111451
        ];
    }
}
