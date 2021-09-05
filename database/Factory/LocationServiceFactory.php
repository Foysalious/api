<?php namespace Factory;

use Sheba\Dal\LocationService\LocationService;

class LocationServiceFactory extends Factory
{
    protected function getModelClass()
    {
        return LocationService::class;
    }
    protected function getData()
    {
        return [
            'location_id' => 4,
            'service_id' => 1
        ];
    }
}