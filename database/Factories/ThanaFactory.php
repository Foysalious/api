<?php

namespace Database\Factories;

use App\Models\Thana;

class ThanaFactory extends Factory
{
    protected $model = Thana::class;

    public function definition(): array
    {
        return [
            'district_id' => 1,
            'location_id' => 4,
            'name'        => 'Gulshan',
            'bn_name'     => 'গুলশান',
            'lat'         => 23.7924960,
            'lng'         => 90.4078060,
        ];
    }
}
