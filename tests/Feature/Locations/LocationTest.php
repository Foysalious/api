<?php namespace Tests\Feature\Locations;

use Tests\Feature\FeatureTestCase;

class LocationTest extends FeatureTestCase
{
    public function testGetLocations()
    {
        $this->json('GET', '/v1/locations')->seeJsonStructure([
            'code',
            'locations' => [
                '*' => ['id', 'name']
            ],
            'msg'
        ]);
    }

    public function testCurrentLocations()
    {
        $this->json('GET', '/v2/locations/current', ['lat' => 23.8270444, 'lng' => 90.3613735])->seeJsonStructure([
            'code',
            'location' => ['id', 'name'],
            'message'
        ]);
    }

}
