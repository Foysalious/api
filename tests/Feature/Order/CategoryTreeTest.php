<?php

namespace Tests\Feature\Order;

use App\Models\Location;
use Factory\UniversalSlugsFactory;
use Illuminate\Support\Facades\Artisan;
use Sheba\Cache\Category\Tree\CategoryTreeCache;
use Sheba\Cache\Category\Tree\CategoryTreeCacheRequest;
use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;
use Tests\Feature\FeatureTestCase;

use Sheba\Cache\CacheAside;
use Sheba\Cache\Location\LocationCacheRequest;

use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Throwable;

class CategoryTreeTest extends FeatureTestCase
{
    protected $location;
    protected $secondaryCategory;
    private $masterCategory;
    private $secondaryCategory2;

    public function setUp(): void
    {
        parent::setUp();

        $cache_aside = app(CacheAside::class);
        $category_tree_cache_request = app(CategoryTreeCacheRequest::class);
        $category_tree_cache_request->setLocationId(4);
        $cache_aside->setCacheRequest($category_tree_cache_request)->deleteEntity();

        $this->truncateTables([
            Category::class,
            CategoryLocation::class,
        ]);

        $this->location = Location::find(4);
        $this->masterCategory = Category::factory()->create();
        $this->secondaryCategory = Category::factory()->create([
            'parent_id' => $this->masterCategory->id,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testPublishedLocationAndCategoryShowInTheList()
    {
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);
        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id,
        ]);
        $service = Service::factory()->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id, ['prices' => 1700]);

        $response = $this->get("v3/categories/tree?location_id=".$this->location->id);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data["code"]);
    }

    public function testUntaggedMasterCategoryWithLocationNotShowInTheList()
    {
        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id,
        ]);
        $service = Service::factory()->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=".$this->location->id);
        $data = $response->decodeResponseJson();
        $this->assertNotTrue(in_array($this->masterCategory->id, $data));
    }

    public function testUntaggedSecondaryCategoryWithLocationNotShowInTheList()
    {
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);

        $service = Service::factory()->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=".$this->location->id);
        $data = $response->decodeResponseJson();
        $this->assertNotTrue($this->secondaryCategory->id, $data);
    }

    public function testUnpublishedMasterCategoryWithLocationNotShowInTheList()
    {
        $this->masterCategory->update(['publication_status' => 0]);
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);

        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id,
        ]);

        $service = Service::factory()->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=".$this->location->id);
        $data = $response->decodeResponseJson();
        $this->assertNotTrue(in_array($this->masterCategory->id, $data));
    }

    public function testUnpublishedSecondaryCategoryWithLocationNotShowInTheList()
    {
        $this->secondaryCategory2 = Category::factory()->create([
            'parent_id' => $this->masterCategory->id,

        ]);
        $this->secondaryCategory2->update(['publication_status' => 0]);

        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);

        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id,
        ]);

        $service = Service::factory()->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=".$this->location->id);
        $data = $response->decodeResponseJson();

        $this->assertNotTrue(in_array($this->secondaryCategory2->id, $data));
    }

    public function test404ForUnpublishedLocation()
    {
        $new_location = Location::factory()->create();
        $new_location->update(['publication_status' => 0]);
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $new_location->id,
        ]);
        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $new_location->id,
        ]);
        $service = Service::factory()->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($new_location->id);

        $response = $this->get("v3/categories/tree?location_id=".$new_location->id);
        $data = $response->decodeResponseJson();
        $this->assertEquals(404, $data['code']);
    }

    public function testAPIReturning3Keys()
    {
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);

        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id,
        ]);

        $service = Service::factory()->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=".$this->location->id);
        $data = $response->decodeResponseJson();
        $this->arrayHasKeys(['code', 'message', 'categories'], $data);
    }

    public function testSlugWithValueOnMasterCategoryArray()
    {
        $this->masterCategory->update(['slug' => 'master_category']);
        CategoryLocation::factory()->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id,
        ]);

        CategoryLocation::factory()->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id,
        ]);

        $service = Service::factory()->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=".$this->location->id);
        $response->decodeResponseJson();
    }
}
