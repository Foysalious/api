<?php

namespace Database\Factories;

use Sheba\Dal\LocationService\LocationService;

class LocationServiceFactory extends Factory
{
    protected $model = LocationService::class;

    public function definition(): array
    {
        return [
            'location_id' => 4,
            'service_id'  => 1,
        ];
    }
}
