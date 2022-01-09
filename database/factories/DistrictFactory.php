<?php
namespace Database\Factories;

use App\Models\District;

class DistrictFactory extends Factory
{
    protected $model = District::class;

    public function definition(): array
    {
        return [
            'division_id' => 1,
            'name'        => 'Dhaka',
            'bn_name'     => 'ঢাকা',
            'lat'         => 23.7115253,
            'lng'         => 90.4111451,
        ];
    }
}
