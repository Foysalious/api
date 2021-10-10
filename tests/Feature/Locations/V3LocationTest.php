<?php namespace Tests\Feature\Locations;

use App\Models\Location;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Location\LocationCacheRequest;
use Tests\Feature\FeatureTestCase;

class V3LocationTest extends FeatureTestCase
{
    public function testResponseReturnsTwoCities()
    {
        $response = $this->get("/v3/locations/");
        $data = $response->decodeResponseJson();
        $locations = collect($data['cities']);
        $this->assertEquals(2, $locations->count());
    }

    public function testNewlyCreatedAndPublishedLocationIsAvailableOnTheList()
    {
        $cache_aside = app(CacheAside::class);
        $location_cache_request = app(LocationCacheRequest::class);
        $cache_aside->setCacheRequest($location_cache_request)->deleteEntity();

        $new_location = factory(Location::class)->create();
        $new_location -> update(["publication_status" => 1]);
        $response = $this->get("/v3/locations");
        $data = $response->decodeResponseJson();
        $city_wise_location_id = $data['cities'][0]['locations'];
        $location_ids_under_city = array_pluck($city_wise_location_id, 'id');
        $this->assertTrue(in_array($new_location->id, $location_ids_under_city));
    }

    public function  testNewlyPublishedLocationUnderCityIsAvailableOnTheList()
    {
        $cache_aside = app(CacheAside::class);
        $location_cache_request = app(LocationCacheRequest::class);
        $cache_aside->setCacheRequest($location_cache_request)->deleteEntity();

        $vatara = Location::find(124);
        $vatara->update(["publication_status" => 1]);
        $response = $this->get("/v3/locations/");
        $data = $response->decodeResponseJson();
        $city_wise_location_id = $data['cities'][0]['locations'];
        $location_ids_under_city = array_pluck($city_wise_location_id, 'id');
        $this->assertTrue(in_array(124,$location_ids_under_city));
    }

    public function testUnpublishedLocationUnderCityUnavailableOnTheList()
    {
        $cache_aside = app(CacheAside::class);
        $location_cache_request = app(LocationCacheRequest::class);
        $cache_aside->setCacheRequest($location_cache_request)->deleteEntity();

        $bashundhara = Location::find(15);
        $bashundhara ->update(["publication_status" => 0]);
        $response = $this->get("/v3/locations/");
        $data = $response->decodeResponseJson();
        $city_wise_location_id = $data['cities'][0]['locations'];
        $location_ids_under_city = array_pluck($city_wise_location_id, 'id');
        $this->assertFalse(in_array(15,$location_ids_under_city));

    }

    public function testNewlyCreatedButUnpublishedLocationUnavailableOnTheList()
    {

            $cache_aside = app(CacheAside::class);
            $location_cache_request = app(LocationCacheRequest::class);
            $cache_aside->setCacheRequest($location_cache_request)->deleteEntity();

            $new_location = factory(Location::class)->create();
            $new_location -> update(["publication_status" => 0]);
            $response = $this->get("/v3/locations");
            $data = $response->decodeResponseJson();
            $city_wise_location_id = $data['cities'][0]['locations'];
            $location_ids_under_city = array_pluck($city_wise_location_id, 'id');
            $this->assertNotTrue(in_array($new_location->id, $location_ids_under_city));

    }

    public function  testWithoutPolygonLocationsNotAvailableOnList()
    {
        $cache_aside = app(CacheAside::class);
        $location_cache_request = app(LocationCacheRequest::class);
        $cache_aside->setCacheRequest($location_cache_request)->deleteEntity();

        $response = $this->get("/v1/locations/");
        $data = $response->decodeResponseJson();
        $locations = collect($data['locations']);
        $location_ids = $locations->pluck('id')->toArray();
        $this->assertNotTrue(in_array(19,$location_ids));

    }

    /*public function testRestoflocationNotShowInTheList()
    {
        $cache_aside = app(CacheAside::class);
        $location_cache_request = app(LocationCacheRequest::class);
        $cache_aside->setCacheRequest($location_cache_request)->deleteEntity();

        $new_rest_of_location = factory(Location::class)->create();
        $new_rest_of_location -> update([
             "city_id" => 2,
            "name" => 'rest_of_ctg',
            "geo_informations"=>'{"lat":22.3569,"lng":91.7832,"radius":"1.1","geometry":{"type":"Polygon","coordinates":[[[90.3898,23.75835],[90.38458,23.75791],[90.38449,23.75685],[90.38445,23.75499],[90.3855,23.75495],[90.38664,23.755],[90.38877,23.75475],[90.38967,23.7566],[90.38998,23.758],[90.3898,23.75835],[90.3898,23.75835]]]},"center":{"lat":23.75655,"lng":90.387215}}'
        ]);
       // dd($new_rest_of_location);
        $response = $this->get("/v3/locations");
        $data = $response->decodeResponseJson();
        $city_wise_location_id = $data['cities'][1]['locations'];
        dd($city_wise_location_id);
        $location_ids_under_city = array_pluck($city_wise_location_id, 'id');
        //  dd(123,in_array($new_location->id, $location_ids_under_city));
        $this->assertTrue(in_array($new_rest_of_location->id, $location_ids_under_city));

    }*/

    public function testPublishLocationNotShowForNullGeoValueInTheList()
    {
        $cache_aside = app(CacheAside::class);
        $location_cache_request = app(LocationCacheRequest::class);
        $cache_aside->setCacheRequest($location_cache_request)->deleteEntity();

        $banani = Location::find(9);

        $banani ->update([
            "publication_status" => 1,
            "geo_informations" => '{"lat":"","lng":"","radius":"1"}'
        ]);
        $response = $this->get("/v3/locations/");
        $data = $response->decodeResponseJson();
        $city_wise_location_id = $data['cities'][0]['locations'];
        $location_ids_under_city = array_pluck($city_wise_location_id, 'id');
        $this->assertFalse(in_array(9,$location_ids_under_city));

    }

    public function testPublishedLocationNotShowForNegGeoValueInTheList()
    {
        $cache_aside = app(CacheAside::class);
        $location_cache_request = app(LocationCacheRequest::class);
        $cache_aside->setCacheRequest($location_cache_request)->deleteEntity();

        $banani = Location::find(9);

        $banani ->update([
            "publication_status" => 1,
            "geo_informations" => '{"lat":"-23.79257905782283","lng":"-90.40352088147586","radius":"1"}'
        ]);
        $response = $this->get("/v3/locations/");
        $data = $response->decodeResponseJson();
        $city_wise_location_id = $data['cities'][0]['locations'];
        $location_ids_under_city = array_pluck($city_wise_location_id, 'id');
        $this->assertFalse(in_array(9,$location_ids_under_city));

    }

    public function testPublishedLocationNotShowForAlphanumericValueInTheList()
    {
        $cache_aside = app(CacheAside::class);
        $location_cache_request = app(LocationCacheRequest::class);
        $cache_aside->setCacheRequest($location_cache_request)->deleteEntity();

        $banani = Location::find(9);

        $banani ->update([
            "publication_status" => 1,
            "geo_informations" => '{"lat":"-23.792579ASSUI","lng":"UIUBN0.40352088147586","radius":"1"}'
        ]);
        $response = $this->get("/v3/locations/");
        $data = $response->decodeResponseJson();
        $city_wise_location_id = $data['cities'][0]['locations'];
        $location_ids_under_city = array_pluck($city_wise_location_id, 'id');
        $this->assertFalse(in_array(9,$location_ids_under_city));

    }
}