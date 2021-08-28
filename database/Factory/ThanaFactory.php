<?php namespace Factory;

use App\Models\Thana;

class ThanaFactory extends Factory
{
    protected function getModelClass()
    {
        return Thana::class;
    }

    protected function getData()
    {
        return [
            'district_id' => 1,
            'location_id' => 4,
            'name' => 'Gulshan',
            'bn_name' => 'গুলশান',
            'lat' => 23.7924960,
            'lng' => 90.4078060
        ];
    }
}