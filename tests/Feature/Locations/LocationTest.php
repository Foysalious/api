<?php namespace Tests\Feature\Locations;

use App\Models\Location;
use Tests\Feature\FeatureTestCase;

class LocationTest extends FeatureTestCase
{
    public function testPublishedLocationCountMatches()
    {
        $response = $this->get("/v1/locations/");
        $data = $response->decodeResponseJson();
        //dd($data);
        $locations = collect($data['locations']);
        $this->assertEquals(79, $locations->count());

    }

    public function  testNewlyPublishedLocationIsAvailableOnTheList()
    {
        $ctg = Location::find(16);
        $ctg->update(["publication_status" => 1]);
        $response = $this->get("/v1/locations/");
        $data = $response->decodeResponseJson();
        $locations = collect($data['locations']);
        $location_ids = $locations->pluck('id')->toArray();
        $this->assertTrue(in_array(16,$location_ids));
    }

    public function  testNewlyUnpublishedLocationIsUnavailableOnTheList()
    {
        $mohammadpur = Location::find(1);
        $mohammadpur->update(["publication_status" => 0]);
        $response = $this->get("/v1/locations/");
        $data = $response->decodeResponseJson();
        $locations = collect($data['locations']);
        $location_ids = $locations->pluck('id')->toArray();
        $this->assertNotTrue(in_array(1,$location_ids));
    }

    public function  testWithoutPolygonLocationsNotAvailableOnList()
    {
        $response = $this->get("/v1/locations/");
        $data = $response->decodeResponseJson();
        $locations = collect($data['locations']);
        $location_ids = $locations->pluck('id')->toArray();
        $this->assertNotTrue(in_array(1,$location_ids));

    }

    public function  testWithRestLocationsNotAvailableOnList()
    {
        $rest_of_dhaka = Location::find(10);
        $rest_of_dhaka->update(["publication_status" => 1]);
        $response = $this->get("/v1/locations/");
        $data = $response->decodeResponseJson();
        $locations = collect($data['locations']);
        $location_ids = $locations->pluck('id')->toArray();
        $this->assertNotTrue(in_array(10,$location_ids));

    }
    public function testIsPublishedForPartnerMatches()
    {
        $response = $this->get("/v1/locations?for=partner");
        $data = $response->decodeResponseJson();
        $locations = collect($data['locations']);
        $this->assertEquals(150, $locations->count());
    }

    public function testNewlyCreatedAndPublishedForPartnerLocationIsAvailableOnTheList()
    {

        $new_location = factory(Location::class)->create();
        $response = $this->get("/v1/locations?for=partner");
        $data = $response->decodeResponseJson();
        $locations = collect($data['locations']);
        $location_ids = $locations->pluck('id')->toArray();
        $this->assertTrue(in_array($new_location->id,$location_ids));
    }

    public function  testNewlyUnpublishedLocationForPartnerIsUnavailableOnTheList()
    {
        $mohammadpur = Location::find(1);
        $mohammadpur->update(["is_published_for_partner" => 0]);
        $response = $this->get("/v1/locations?for=partner");
        $data = $response->decodeResponseJson();
        $locations = collect($data['locations']);
        $location_ids = $locations->pluck('id')->toArray();
        $this->assertNotTrue(in_array(1,$location_ids));
    }

}
