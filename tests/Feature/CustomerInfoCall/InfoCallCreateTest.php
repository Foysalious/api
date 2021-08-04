<?php namespace Tests\Feature\CustomerInfoCall;

use Tests\Feature\FeatureTestCase;

class InfoCallCreateTest extends FeatureTestCase
{

    public function testInfoCallCreateCustomerForResponse200()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile' => '01620011019',
            'location_id' => 4,
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
    }

    public function testInfoCallCreateResponseWithoutLocationId()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile' => '01620011019',
            'location_id' => '',
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The location id field is required.", $data["message"]);

    }

    public function testInfoCallCreateResponseWithoutMobile()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile' => '',
            'location_id' => 4,
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The mobile field is required.", $data["message"]);

    }

    public function testInfoCallCreateResponseWithoutServiceName()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => '',
            'mobile' => '01620011019',
            'location_id' => 4,
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The service name field is required.", $data["message"]);

    }

    public function testInfoCallCreateResponseWithWrongMobileNumber()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile' => '01534546',
            'location_id' => 4,
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data["message"]);

    }

    public function testInfoCallCreateForResponse200WithCountryCodeMobile()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile' => '+8801620011019',
            'location_id' => 4,
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testInfoCallCreateResponseWithWrongLocationId()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile' => '01620011019',
            'location_id' => 99999999999999999999,
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals("Location Not Found", $data["message"]);

    }

    public function testInfoCallCreateResponseWithCharactersInMobileNumber()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile' => 'qwertyuiop',
            'location_id' => 4,
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data["message"]);

    }

    public function testInfoCallCreateResponseWithSpecialCharactersInServiceName()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => '!@#$%^&*',
            'mobile' => '01620011019',
            'location_id' => 4,
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testInfoCallCreateResponseWithWrongServiceNameMobileLocationId()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => '',
            'mobile' => '',
            'location_id' => '',
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The service name field is required.The mobile field is required.The location id field is required.", $data["message"]);

    }

    public function testInfoCallCreateForResponseWithGetMethod()
    {

        //arrange

        //act
        $response = $this->get("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile' => '01620011019',
            'location_id' => 4,
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals("405 Method Not Allowed", $data["message"]);

    }

    public function testInfoCallCreateForResponseWithMobileInParameterAndServiceNameLocationIdInBody()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call?mobile=01620011019", [
            'service_name' => 'Hand maid pizza',
            'location_id' => 4,
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testInfoCallCreateForResponseWithServiceNameMobileLocationIdInParameter()
    {

        //arrange

        //act
        $response = $this->post("/v3/customers/info-call?service_name=Hand maid pizza&mobile=01620011019&location_id=4");

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

}