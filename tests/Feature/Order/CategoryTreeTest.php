<?php namespace Tests\Feature\Order;

use App\Models\Location;
use Factory\UniversalSlugsFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Sheba\Cache\Category\Tree\CategoryTreeCacheRequest;
use Sheba\Dal\Service\Service;
use Tests\Feature\FeatureTestCase;

use Sheba\Cache\CacheAside;

use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;



class CategoryTreeTest extends FeatureTestCase
{
    protected $location;
    protected $secondaryCategory;
    protected $secondaryCategory2;
    private $masterCategory;
    private $new_location;

    /**
     * @var Collection|Model|mixed
     */
    public function setUp(): void
    {
        parent::setUp();

        $cache_aside = app(CacheAside::class);
        $category_tree_cache_request = app(CategoryTreeCacheRequest::class);
        $category_tree_cache_request->setLocationId(4);
        $cache_aside->setCacheRequest($category_tree_cache_request)->deleteEntity();

        $this->truncateTables([
            Category::class,
            CategoryLocation::class
        ]);

        $this->location = Location::find(4);
        $this->masterCategory = factory(Category::class)->create();
        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $this->masterCategory->id
        ]);
    }

    public function testPublishedLocationAndCategoryShowInTheList()
    {
        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);
        $service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id, ['prices' => 1700]);

        $response = $this->get("v3/categories/tree?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data["code"]);

    }

    public function testUntaggedMasterCategoryWithLocationNotShowInTheList()
    {
        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);
        $service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();
        $this->assertNotTrue(in_array($this->masterCategory->id, $data));

    }

    public function testUntaggedSecondaryCategoryWithLocationNotShowInTheList()
    {
        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);

        $service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();
        $this->assertNotTrue($this->secondaryCategory->id, $data);
    }

    public function testUnpublishedMasterCategoryWithLocationNotShowInTheList(){

        $this->masterCategory-> update(['publication_status' => 0]);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        $service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();
        $this->assertNotTrue(in_array($this->masterCategory->id, $data));

    }

    public function testUnpublishedSecondaryCategoryWithLocationNotShowInTheList()
    {

        $this->secondaryCategory2 = factory(Category::class)->create([
            'parent_id' => $this->masterCategory->id

        ]);
        $this->secondaryCategory2->update(['publication_status' => 0]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        $service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        $this->assertNotTrue(in_array($this->secondaryCategory2->id, $data));

    }

    public function test404ForUnpublishedLocation()
    {
        $new_location = factory(Location::class)->create();
        $new_location->update(['publication_status' => 0]);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $new_location->id
        ]);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $new_location->id
        ]);
        $service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($new_location->id);

        $response = $this->get("v3/categories/tree?location_id=" . $new_location->id);
        $data = $response->decodeResponseJson();
        $this->assertEquals(404, $data['code']);
    }

    public function testAPIReturning3Keys()
    {
        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        $service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();
        $this->arrayHasKeys(['code', 'message', 'categories'], $data);
    }

    public function testSlugWithValueOnMasterCategoryArray()
    {
        $this->masterCategory->update(['slug' => 'master_category']);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        $service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=".$this->location->id);
        $data = $response->decodeResponseJson();
    }

   /* public function testSlugWithValueOnSecondaryCategoryArray()
    {
        $this->slug = factory (UniversalSlugsFactory::class)->create();

        $this->secondaryCategory->update([
            'slug' => $this->slug]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);


        $service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
        ]);
        $service->locations()->attach($this->location->id);

        $response = $this->get("v3/categories/tree?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();
        */


}
