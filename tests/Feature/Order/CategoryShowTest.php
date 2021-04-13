<?php namespace Tests\Feature\Order;

use App\Models\Location;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;
use Tests\Feature\FeatureTestCase;

class CategoryShowTest extends FeatureTestCase
{
    /** @var Location */
   // private $location;
    /** @var Category */
   // private $secondaryCategory;

    public function setUp()
    {
        parent::setUp();

        $this->location = Location::find(1);
        $this->truncateTables([
            Category::class,
            Service::class,
            CategoryLocation::class,
            LocationService::class
        ]);
        $master_category = factory(Category::class)->create();

        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $master_category->id,
            'publication_status' => 1
        ]);
        $this->secondaryCategory->locations()->attach($this->location->id);
    }

    public function testOnlyPublishedAndLocationTaggedServicesShouldBeInCategoryDetails()
    {
        $service_1 = $this->createService(true, true);
        $service_2 = $this->createService(false, true);
        $service_3 = $this->createService(true, false);
        $service_4 = $this->createService(true, true);

        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $response->assertResponseOk();
        $data = $response->decodeResponseJson();
        $services = collect($data['category']['services']);

        $this->assertEquals(2, $services->count());
        $expected_service_ids = [$service_1->id, $service_4->id];
        $actual_service_ids = $services->pluck('id')->toArray();
        $this->assertTrue(hasSameValues($expected_service_ids, $actual_service_ids));
    }

    /**
     * @param $is_published
     * @param $is_tagged
     * @return Service
     */
    private function createService($is_published, $is_tagged)
    {
        /** @var Service $service_1 */
        $service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'variable_type' => ServiceType::FIXED,
            'variables' => '{"price":"1700","min_price":"1000","max_price":"2500","description":""}',
            'publication_status' => $is_published ? 1 : 0
        ]);
        if ($is_tagged) $service->locations()->attach($this->location->id, ['prices' => 1700]);
        return $service;
    }
}
