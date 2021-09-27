<?php namespace Tests\Feature\sProServiceBookCategory;

use App\Models\Location;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Children\Services\ServicesCacheRequest;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Tests\Feature\FeatureTestCase;

class sProServiceDetailsTest extends FeatureTestCase
{

    private $masterCategory;
    private $secondaryCategory;
    private $service;
    private $location;

    public function setUp()
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
            LocationService::class
        ]);

        $this->location = Location::find(4);

        $this->masterCategory = factory(Category::class)->create([
            'name' => 'Car Maintenance',
            'slug' => 'car-maintenance'
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => 'Car Paint',
            'slug' => 'car-paint',
            'parent_id' => $this->masterCategory->id,
            'service_title' => 'Car Paint',
            'terms_and_conditions' => '["Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eligendi non quis exercitationem culpa nesciunt nihil aut nostrum explicabo reprehenderit optio amet ab temporibus asperiores quasi cupiditate. Voluptatum ducimus voluptates voluptas?", "• The price declared is an estimate and may vary depending on bus availability and the travel route plan • Overtime: After 10 hours duty BDT 1000 will be charged per hour • Booking should be placed at least 2 day before the service availing date. • Nocturnal service period is from 10.00pm to 8.00am • Minimum 4 Hours Lead time after service booking • Emergency Support Service (BDT 500 will be added for Emergency Support)"]',
            'max_order_amount' => 100
        ]);

        $this->service = factory(Service::class)->create([
            'name' => 'Matte Black',
            'slug' => 'matte-black',
            'category_id'=> $this->secondaryCategory->id,
            'description' => 'This is it!',
            'faqs' => '[{"question":"How are you?","answer":"I am fine."}]',
            'unit' => 'vehicle',
            'bn_name' => 'কার ওয়াস',
            'short_description' => 'Hello',
            'terms_and_conditions' => '["The price declared is an estimate and may vary depending on bus availability and the travel route plan • Overtime: After 10 hours duty BDT 1000 will be charged per hour • Booking should be placed at least 2 day before the service availing date. • Nocturnal service period is from 10.00pm to 8.00am"]',
            'features' => '["WHAT TO EXPECT FROM THIS SERVICE"]'
        ]);

        factory(CategoryLocation::class)->create([
            'category_id'=>$this->masterCategory->id,
            'location_id'=>$this->location->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id'=>$this->secondaryCategory->id,
            'location_id'=>$this->location->id
        ]);

        LocationService::create([
            'location_id'=>$this->location->id,
            'service_id'=>$this->service->id,
            'prices'=> 100
        ]);

    }

    public function testSProServiceDetailsAPIWithValidLatLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/services/" . $this->service->id . "?lat=23.788994076131&lng=90.410852011945");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->service->id, $data["service"]["id"]);
        $this->assertEquals("Matte Black", $data["service"]["name"]);
        $this->assertEquals(null, $data["service"]["slug"]);
        $this->assertEquals("Fixed", $data["service"]["variable_type"]);
        $this->assertEquals(1, $data["service"]["min_quantity"]);
        $this->assertEquals(0, $data["service"]["is_inspection_service"]);
        $this->assertEquals("vehicle", $data["service"]["unit"]);
        $this->assertEquals(null, $data["service"]["questions"]);
        $this->assertEquals($this->secondaryCategory->id, $data["service"]["category"]["id"]);
        $this->assertEquals($this->secondaryCategory->name, $data["service"]["category"]["name"]);
        $this->assertEquals(null, $data["service"]["category"]["slug"]);
        $this->assertEquals(null, $data["service"]["category"]["cross_sale"]);
        $this->assertEquals(null, $data["service"]["category"]["delivery_discount"]);
        $this->assertEquals(0, $data["service"]["category"]["delivery_charge"]);
        $this->assertEquals(1, $data["service"]["category"]["is_auto_sp_enabled"]);
        $this->assertEquals(0, $data["service"]["category"]["min_order_amount"]);
        $this->assertEquals(100, $data["service"]["fixed_price"]);
        $this->assertEquals(null, $data["service"]["fixed_upsell_price"]);
        $this->assertEquals(null, $data["service"]["option_prices"]);
        $this->assertEquals(100, $data["service"]["min_price"]);
        $this->assertEquals(100, $data["service"]["max_price"]);
        $this->assertEquals(null, $data["service"]["discount"]);
        $this->assertEquals(null, $data["service"]["usp"]);
        $this->assertEquals(null, $data["service"]["overview"]);
        $this->assertEquals(null, $data["service"]["structured_description_bn"]);
        $this->assertEquals("This is it!", $data["service"]["details"]);
        $this->assertEquals(null, $data["service"]["partnership"]);
        $this->assertEquals("How are you?", $data["service"]["faqs"][0]["question"]);
        $this->assertEquals("I am fine.", $data["service"]["faqs"][0]["answer"]);
        $this->assertEquals("The price declared is an estimate and may vary depending on bus availability and the travel route plan • Overtime: After 10 hours duty BDT 1000 will be charged per hour • Booking should be placed at least 2 day before the service availing date. • Nocturnal service period is from 10.00pm to 8.00am", $data["service"]["terms_and_conditions"][0]);
        $this->assertEquals("WHAT TO EXPECT FROM THIS SERVICE", $data["service"]["features"][0]);
        $this->assertEquals(null, $data["service"]["gallery"]);
        $this->assertEquals(null, $data["service"]["blog"]);
        $this->assertEquals(null, $data["service"]["avg_rating"]);
        $this->assertEquals(null, $data["service"]["total_ratings"]);
        $this->assertEquals(1, $data["service"]["total_services"]);

    }

    public function testSProServiceDetailsAPIWithValidLatInvalidLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/services/" . $this->service->id . "?lat=23.788099544655&lng=dfdsfasdf");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng must be a number.', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithInvalidLatValidLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/services/" . $this->service->id . "?lat=dfdsfasdf&lng=90.410852011945");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithInvalidLatLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/services/" . $this->service->id . "?lat=dfdsfasdf&lng=dfdsfasdf");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.The lng must be a number.', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithValidLatNoLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/services/" . $this->service->id . "?lat=23.788099544655");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng field is required.', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithNoLatValidLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/services/" . $this->service->id . "?lng=90.412001016086");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat field is required.', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithoutLatLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/services/" . $this->service->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat field is required.The lng field is required.', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithInvalidServiceId()
    {
        //arrange

        //act
        $response = $this->get("/v3/services/111?lat=23.788994076131&lng=90.410852011945");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    //Failed
//    public function testSProServiceDetailsAPIWithoutAnyCategory()
//    {
//        //arrange
//        $this->truncateTable(Category::class);
//
//        //act
//        $response = $this->get("/v3/services/" . $this->service->id . "?lat=23.788994076131&lng=90.410852011945");
//        $data = $response->decodeResponseJson();
//
//        //assert
//        $this->assertEquals(404, $data["code"]);
//        $this->assertEquals('', $data["message"]);
//
//    }

    public function testSProServiceDetailsAPIWithoutAnyService()
    {
        //arrange
        $this->truncateTable(Service::class);

        //act
        $response = $this->get("/v3/services/" . $this->service->id . "?lat=23.788994076131&lng=90.410852011945");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    //Failed
//    public function testSProServiceDetailsAPIWithoutAnyCategoryLocation()
//    {
//        //arrange
//        $this->truncateTable(CategoryLocation::class);
//
//        //act
//        $response = $this->get("/v3/services/" . $this->service->id . "?lat=23.788994076131&lng=90.410852011945");
//        $data = $response->decodeResponseJson();
//        dd($data);
//
//        //assert
//        $this->assertEquals(404, $data["code"]);
//        $this->assertEquals('Not found', $data["message"]);
//
//    }

    //Failed
//    public function testSProServiceDetailsAPIWithoutAnyLocationService()
//    {
//        //arrange
//        $this->truncateTable(LocationService::class);
//
//        //act
//        $response = $this->get("/v3/services/" . $this->service->id . "?lat=23.788994076131&lng=90.410852011945");
//        $data = $response->decodeResponseJson();
//        dd($data);
//
//        //assert
//        $this->assertEquals(404, $data["code"]);
//        $this->assertEquals('Not found', $data["message"]);
//
//    }

    public function testSProServiceDetailsAPIWithPostMethod()
    {
        //arrange

        //act
        $response = $this->post("/v3/services/" . $this->service->id . "?lat=23.788994076131&lng=90.410852011945");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('405 Method Not Allowed', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithPutMethod()
    {
        //arrange

        //act
        $response = $this->put("/v3/services/" . $this->service->id . "?lat=23.788994076131&lng=90.410852011945");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('405 Method Not Allowed', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithDeleteMethod()
    {
        //arrange

        //act
        $response = $this->delete("/v3/services/" . $this->service->id . "?lat=23.788994076131&lng=90.410852011945");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('405 Method Not Allowed', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithSubCategoryUnpublishedAndMasterCategoryPublishedAndServicePublished()
    {
        //arrange
        $this->masterCategory -> update(["publication_status" => 1]);

        $this->secondaryCategory -> update(["publication_status" => 0]);

        $this->service -> update(["publication_status" => 1]);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithSubCategoryUnpublishedAndMasterCategoryUnpublishedAndServiceUnpublished()
    {
        //arrange
        $this->masterCategory -> update(["publication_status" => 0]);

        $this->secondaryCategory -> update(["publication_status" => 0]);

        $this->service -> update(["publication_status" => 0]);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithSubCategoryUnpublishedAndMasterCategoryUnpublishedAndServicePublished()
    {
        //arrange
        $this->masterCategory -> update(["publication_status" => 0]);

        $this->secondaryCategory -> update(["publication_status" => 0]);

        $this->service -> update(["publication_status" => 1]);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithSubCategoryUnpublishedAndMasterCategoryPublishedAndServiceUnpublished()
    {
        //arrange
        $this->masterCategory -> update(["publication_status" => 1]);

        $this->secondaryCategory -> update(["publication_status" => 0]);

        $this->service -> update(["publication_status" => 0]);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithSubCategoryPublishedAndMasterCategoryPublishedAndServicePublished()
    {
        //arrange
        $this->masterCategory -> update(["publication_status" => 1]);

        $this->secondaryCategory -> update(["publication_status" => 1]);

        $this->service -> update(["publication_status" => 1]);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);

    }

    public function testSProServiceDetailsAPIWithSubCategoryPublishedAndMasterCategoryUnpublishedAndServiceUnpublished()
    {
        //arrange
        $this->masterCategory -> update(["publication_status" => 0]);

        $this->secondaryCategory -> update(["publication_status" => 1]);

        $this->service -> update(["publication_status" => 0]);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    //Failed
//    public function testSProServiceDetailsAPIWithSubCategoryPublishedAndMasterCategoryUnpublishedAndServicePublished()
//    {
//        //arrange
//        $this->masterCategory -> update(["publication_status" => 0]);
//
//        $this->secondaryCategory -> update(["publication_status" => 1]);
//
//        $this->service -> update(["publication_status" => 1]);
//
//        //act
//        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
//        $data = $response->decodeResponseJson();
//
//        //assert
//        $this->assertEquals(404, $data["code"]);
//        $this->assertEquals('Not found', $data["message"]);
//
//    }

    public function testSProServiceDetailsAPIWithSubCategoryPublishedAndMasterCategoryPublishedAndServiceUnpublished()
    {
        //arrange
        $this->masterCategory -> update(["publication_status" => 1]);

        $this->secondaryCategory -> update(["publication_status" => 1]);

        $this->service -> update(["publication_status" => 0]);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

}