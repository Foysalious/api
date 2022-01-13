<?php

namespace Tests\Feature\Order;

use App\Models\Location;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Children\Services\ServicesCacheRequest;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Tests\Feature\FeatureTestCase;


class ServiceLocationTest extends featureTestCase
{
    protected $location;
    protected $secondaryCategory;
    private $masterCategory;
    private $service1;
    private $service2;
    private $location2;


    public function setUp(): void
    {
        parent::setUp();
        $cache_aside = app(CacheAside::class);
        $service_cache_request = app(ServicesCacheRequest::class);
        $service_cache_request->setLocationId(4);
        $cache_aside->setCacheRequest($service_cache_request)->deleteEntity();

        $this->truncateTables([
            Category::class,
            CategoryLocation::class,
            Service::class,
            LocationService::class,

        ]);

        $this->location2 = Location::find(10);
        $this->location = Location::find(4);
        $this->masterCategory = Category::factory()->create();
        $this->secondaryCategory = Category::factory()->create([
            'parent_id' => $this->masterCategory->id,
        ]);
        $this->service1 = Service::factory()->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $this->service2 = Service::factory()->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
    }

    public function testPublishedAndTaggedCategoryServiceShowingInResponse()
    {
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);
        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id,
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id'  => $this->service1->id,
            'prices'      => 100,
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id'  => $this->service2->id,
            'prices'      => 200,
        ]);
        $response = $this->get(
            "/v3/categories/".$this->secondaryCategory->id."/services?location_id=".$this->location->id
        );
        $data = $response->decodeResponseJson();

        $this->assertEquals($this->secondaryCategory->id, $data['category']['id']);
        $this->assertEquals($this->service1->id, $data['category']['services'][0]['id']);
        $this->assertEquals($this->service2->id, $data['category']['services'][1]['id']);
    }

    public function testUntaggedButPublishedServiceNotShowingInResponse()
    {
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);
        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id,
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id'  => $this->service1->id,
            'prices'      => 100,
        ]);


        $response = $this->get(
            "/v3/categories/".$this->secondaryCategory->id."/services?location_id=".$this->location->id
        );
        $data = $response->decodeResponseJson();

        $this->assertEquals($this->secondaryCategory->id, $data['category']['id']);
        $services = $data['category']['services'];

        $service_ids = [];
        for ($i = 0; $i < count($services); $i++) {
            array_push($service_ids, $services[$i]['id']);
        }
        $this->assertNotTrue(in_array($this->service2->id, $service_ids));
        $this->assertTrue(in_array($this->service1->id, $service_ids));
    }

    public function testTaggedButUnpublishedServiceNotShowingInResponse()
    {
        $this->service2 = Service::factory()->create([
            'category_id'        => $this->secondaryCategory->id,
            'publication_status' => 0,
        ]);
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);
        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id,
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id'  => $this->service1->id,
            'prices'      => 100,
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id'  => $this->service2->id,
            'prices'      => 200,
        ]);
        $response = $this->get(
            "/v3/categories/".$this->secondaryCategory->id."/services?location_id=".$this->location->id
        );
        $data = $response->decodeResponseJson();

        $this->assertEquals($this->secondaryCategory->id, $data['category']['id']);
        $services = $data['category']['services'];

        $service_ids = [];
        for ($i = 0; $i < count($services); $i++) {
            array_push($service_ids, $services[$i]['id']);
        }
        $this->assertNotTrue(in_array($this->service2->id, $service_ids));
        $this->assertTrue(in_array($this->service1->id, $service_ids));
    }

    public function testApiResponseGiving200ForAllRequiredPram()
    {
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);
        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id,
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id'  => $this->service1->id,
            'prices'      => 100,
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id'  => $this->service2->id,
            'prices'      => 200,
        ]);
        $response = $this->get(
            "/v3/categories/".$this->secondaryCategory->id."/services?location_id=".$this->location->id
        );
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
    }

    public function testApiResponseGiving404ForUntaggedSecondaryCategory()
    {
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id'  => $this->service1->id,
            'prices'      => 100,
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id'  => $this->service2->id,
            'prices'      => 200,
        ]);

        $response = $this->get("/v3/categories/".$this->secondaryCategory->id."/services?location_id=".$this->location->id);
        $data = $response->decodeResponseJson();

        $this->assertEquals(404, $data["code"]);
    }

    public function testApiResponseGiving404ForUntaggedServicesWithLocation()
    {
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);

        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->get(
            "/v3/categories/".$this->secondaryCategory->id."/services?location_id=".$this->location->id
        );
        $data = $response->decodeResponseJson();

        $this->assertEquals(404, $data["code"]);
    }
}
