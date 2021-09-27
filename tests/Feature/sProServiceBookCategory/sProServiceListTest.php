<?php namespace Tests\Feature\sProServiceBookCategory;

use App\Models\Location;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Children\Services\ServicesCacheRequest;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Tests\Feature\FeatureTestCase;

class sProServiceListTest extends FeatureTestCase
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

    public function testSProServiceListAPIWithValidLocationId()
    {
        //arrange

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->secondaryCategory->id, $data["category"]["id"]);
        $this->assertEquals($this->masterCategory->id, $data["category"]["parent_id"]);
        $this->assertEquals("Car Paint", $data["category"]["name"]);
        $this->assertEquals(null, $data["category"]["slug"]);
        $this->assertEquals("Car Paint", $data["category"]["service_title"]);
        $this->assertEquals('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eligendi non quis exercitationem culpa nesciunt nihil aut nostrum explicabo reprehenderit optio amet ab temporibus asperiores quasi cupiditate. Voluptatum ducimus voluptates voluptas?', $data["category"]["terms_and_conditions"][0]);
        $this->assertEquals('• The price declared is an estimate and may vary depending on bus availability and the travel route plan • Overtime: After 10 hours duty BDT 1000 will be charged per hour • Booking should be placed at least 2 day before the service availing date. • Nocturnal service period is from 10.00pm to 8.00am • Minimum 4 Hours Lead time after service booking • Emergency Support Service (BDT 500 will be added for Emergency Support)', $data["category"]["terms_and_conditions"][1]);
        $this->assertEquals(1, $data["category"]["is_auto_sp_enabled"]);
        $this->assertEquals(0, $data["category"]["is_vat_applicable"]);
        $this->assertEquals(0, $data["category"]["min_order_amount"]);
        $this->assertEquals(100, $data["category"]["max_order_amount"]);
        $this->assertEquals(5, $data["category"]["vat_percentage"]);
        $this->assertEquals("Car Maintenance", $data["category"]["parent_name"]);
        $this->assertEquals("car-maintenance", $data["category"]["parent_slug"]);
        $this->assertEquals($this->service->id, $data["category"]["services"][0]["id"]);
        $this->assertEquals($this->secondaryCategory->id, $data["category"]["services"][0]["category_id"]);
        $this->assertEquals("vehicle", $data["category"]["services"][0]["unit"]);
        $this->assertEquals("Matte Black", $data["category"]["services"][0]["name"]);
        $this->assertEquals("কার ওয়াস", $data["category"]["services"][0]["bn_name"]);
        $this->assertEquals("Hello", $data["category"]["services"][0]["short_description"]);
        $this->assertEquals("This is it!", $data["category"]["services"][0]["description"]);
        $this->assertEquals("How are you?", $data["category"]["services"][0]["faqs"][0]["question"]);
        $this->assertEquals("I am fine.", $data["category"]["services"][0]["faqs"][0]["answer"]);
        $this->assertEquals("Fixed", $data["category"]["services"][0]["variable_type"]);
        $this->assertEquals(1, $data["category"]["services"][0]["min_quantity"]);
        $this->assertEquals('The price declared is an estimate and may vary depending on bus availability and the travel route plan • Overtime: After 10 hours duty BDT 1000 will be charged per hour • Booking should be placed at least 2 day before the service availing date. • Nocturnal service period is from 10.00pm to 8.00am', $data["category"]["services"][0]["terms_and_conditions"][0]);
        $this->assertEquals('WHAT TO EXPECT FROM THIS SERVICE', $data["category"]["services"][0]["features"][0]);
        $this->assertEquals(0, $data["category"]["services"][0]["is_inspection_service"]);
        $this->assertEquals(0, $data["category"]["services"][0]["is_add_on"]);
        $this->assertEquals(100, $data["category"]["services"][0]["fixed_price"]);
        $this->assertEquals(null, $data["category"]["services"][0]["fixed_upsell_price"]);
        $this->assertEquals(null, $data["category"]["services"][0]["discount"]);
        $this->assertEquals(100, $data["category"]["services"][0]["max_price"]);
        $this->assertEquals(100, $data["category"]["services"][0]["min_price"]);
        $this->assertEquals(null, $data["category"]["services"][0]["addon"]);
        $this->assertEquals(null, $data["category"]["services"][0]["slug"]);
        $this->assertEquals("normal", $data["category"]["services"][0]["type"]);
        $this->assertEquals(null, $data["category"]["services"][0]["questions"]);
        $this->assertEquals([], $data["category"]["subscriptions"]);
        $this->assertEquals(null, $data["category"]["cross_sale"]);
        $this->assertEquals(0, $data["category"]["delivery_charge"]);
        $this->assertEquals(null, $data["category"]["delivery_discount"]);
        $this->assertEquals(null, $data["category"]["disclaimer"]);

    }

    public function testSProServiceListAPIWithValidLatLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?lat=23.788099544655&lng=90.412001016086");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->secondaryCategory->id, $data["category"]["id"]);
        $this->assertEquals($this->masterCategory->id, $data["category"]["parent_id"]);
        $this->assertEquals("Car Paint", $data["category"]["name"]);
        $this->assertEquals(null, $data["category"]["slug"]);
        $this->assertEquals("Car Paint", $data["category"]["service_title"]);
        $this->assertEquals('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eligendi non quis exercitationem culpa nesciunt nihil aut nostrum explicabo reprehenderit optio amet ab temporibus asperiores quasi cupiditate. Voluptatum ducimus voluptates voluptas?', $data["category"]["terms_and_conditions"][0]);
        $this->assertEquals('• The price declared is an estimate and may vary depending on bus availability and the travel route plan • Overtime: After 10 hours duty BDT 1000 will be charged per hour • Booking should be placed at least 2 day before the service availing date. • Nocturnal service period is from 10.00pm to 8.00am • Minimum 4 Hours Lead time after service booking • Emergency Support Service (BDT 500 will be added for Emergency Support)', $data["category"]["terms_and_conditions"][1]);
        $this->assertEquals(1, $data["category"]["is_auto_sp_enabled"]);
        $this->assertEquals(0, $data["category"]["is_vat_applicable"]);
        $this->assertEquals(0, $data["category"]["min_order_amount"]);
        $this->assertEquals(100, $data["category"]["max_order_amount"]);
        $this->assertEquals(5, $data["category"]["vat_percentage"]);
        $this->assertEquals("Car Maintenance", $data["category"]["parent_name"]);
        $this->assertEquals("car-maintenance", $data["category"]["parent_slug"]);
        $this->assertEquals($this->service->id, $data["category"]["services"][0]["id"]);
        $this->assertEquals($this->secondaryCategory->id, $data["category"]["services"][0]["category_id"]);
        $this->assertEquals("vehicle", $data["category"]["services"][0]["unit"]);
        $this->assertEquals("Matte Black", $data["category"]["services"][0]["name"]);
        $this->assertEquals("কার ওয়াস", $data["category"]["services"][0]["bn_name"]);
        $this->assertEquals("Hello", $data["category"]["services"][0]["short_description"]);
        $this->assertEquals("This is it!", $data["category"]["services"][0]["description"]);
        $this->assertEquals("How are you?", $data["category"]["services"][0]["faqs"][0]["question"]);
        $this->assertEquals("I am fine.", $data["category"]["services"][0]["faqs"][0]["answer"]);
        $this->assertEquals("Fixed", $data["category"]["services"][0]["variable_type"]);
        $this->assertEquals(1, $data["category"]["services"][0]["min_quantity"]);
        $this->assertEquals('The price declared is an estimate and may vary depending on bus availability and the travel route plan • Overtime: After 10 hours duty BDT 1000 will be charged per hour • Booking should be placed at least 2 day before the service availing date. • Nocturnal service period is from 10.00pm to 8.00am', $data["category"]["services"][0]["terms_and_conditions"][0]);
        $this->assertEquals('WHAT TO EXPECT FROM THIS SERVICE', $data["category"]["services"][0]["features"][0]);
        $this->assertEquals(0, $data["category"]["services"][0]["is_inspection_service"]);
        $this->assertEquals(0, $data["category"]["services"][0]["is_add_on"]);
        $this->assertEquals(100, $data["category"]["services"][0]["fixed_price"]);
        $this->assertEquals(null, $data["category"]["services"][0]["fixed_upsell_price"]);
        $this->assertEquals(null, $data["category"]["services"][0]["discount"]);
        $this->assertEquals(100, $data["category"]["services"][0]["max_price"]);
        $this->assertEquals(100, $data["category"]["services"][0]["min_price"]);
        $this->assertEquals(null, $data["category"]["services"][0]["addon"]);
        $this->assertEquals(null, $data["category"]["services"][0]["slug"]);
        $this->assertEquals("normal", $data["category"]["services"][0]["type"]);
        $this->assertEquals(null, $data["category"]["services"][0]["questions"]);
        $this->assertEquals([], $data["category"]["subscriptions"]);
        $this->assertEquals(null, $data["category"]["cross_sale"]);
        $this->assertEquals(0, $data["category"]["delivery_charge"]);
        $this->assertEquals(null, $data["category"]["delivery_discount"]);
        $this->assertEquals(null, $data["category"]["disclaimer"]);

    }

    public function testSProServiceListAPIWithInvalidLocationId()
    {
        //arrange

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=111");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceListAPIWithoutLocationId()
    {
        //arrange

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceListAPIWithValidLatInvalidLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?lat=23.788099544655&lng=dfdsfasdf");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lng must be a number.', $data["message"]);

    }

    public function testSProServiceListAPIWithInvalidLatValidLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?lat=dfdsfasdf&lng=90.410852011945");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.', $data["message"]);

    }

    public function testSProServiceListAPIWithInvalidLatLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?lat=dfdsfasdf&lng=dfdsfasdf");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The lat must be a number.The lng must be a number.', $data["message"]);

    }

    public function testSProServiceListAPIWithValidLatNoLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?lat=23.788099544655");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceListAPIWithNoLatValidLng()
    {
        //arrange

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?lng=90.412001016086");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceListAPIWithInvalidCategoryId()
    {
        //arrange

        //act
        $response = $this->get("/v3/categories/111/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceListAPIWithoutAnyCategory()
    {
        //arrange
        $this->truncateTable(Category::class);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceListAPIWithoutAnyService()
    {
        //arrange
        $this->truncateTable(Service::class);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceListAPIWithoutAnyCategoryLocation()
    {
        //arrange
        $this->truncateTable(CategoryLocation::class);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceListAPIWithoutAnyLocationService()
    {
        //arrange
        $this->truncateTable(LocationService::class);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceListAPIWithPostMethod()
    {
        //arrange

        //act
        $response = $this->post("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('405 Method Not Allowed', $data["message"]);

    }

    public function testSProServiceListAPIWithPutMethod()
    {
        //arrange

        //act
        $response = $this->put("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('405 Method Not Allowed', $data["message"]);

    }

    public function testSProServiceListAPIWithDeleteMethod()
    {
        //arrange

        //act
        $response = $this->delete("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('405 Method Not Allowed', $data["message"]);

    }

    public function testSProServiceListAPIWithSubCategoryUnpublishedAndMasterCategoryPublishedAndServicePublished()
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

    public function testSProServiceListAPIWithSubCategoryUnpublishedAndMasterCategoryUnpublishedAndServiceUnpublished()
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

    public function testSProServiceListAPIWithSubCategoryUnpublishedAndMasterCategoryUnpublishedAndServicePublished()
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

    public function testSProServiceListAPIWithSubCategoryUnpublishedAndMasterCategoryPublishedAndServiceUnpublished()
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

    public function testSProServiceListAPIWithSubCategoryPublishedAndMasterCategoryPublishedAndServicePublished()
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

    public function testSProServiceListAPIWithSubCategoryPublishedAndMasterCategoryUnpublishedAndServiceUnpublished()
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
    public function testSProServiceListAPIWithSubCategoryPublishedAndMasterCategoryUnpublishedAndServicePublished()
    {
        //arrange
        $this->masterCategory -> update(["publication_status" => 0]);

        $this->secondaryCategory -> update(["publication_status" => 1]);

        $this->service -> update(["publication_status" => 1]);

        //act
        $response = $this->get("/v3/categories/" . $this->secondaryCategory->id . "/services?location_id=" . $this->location->id);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);

    }

    public function testSProServiceListAPIWithSubCategoryPublishedAndMasterCategoryPublishedAndServiceUnpublished()
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