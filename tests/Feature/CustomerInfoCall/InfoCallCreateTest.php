<?php

namespace Tests\Feature\CustomerInfoCall;

use Tests\Feature\FeatureTestCase;
use Throwable;

/**
 * @author Mahanaz Tabassum <mahanaz.tabassum@sheba.xyz>
 */
class InfoCallCreateTest extends FeatureTestCase
{
    /**
     * @throws Throwable
     */
    public function testInfoCallCreateCustomerForResponse200()
    {
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => '01620011019',
            'location_id'  => 4,
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateResponseWithoutLocationId()
    {
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => '01620011019',
            'location_id'  => '',
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The location id field is required.", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateResponseWithoutMobile()
    {
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => '',
            'location_id'  => 4,
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The mobile field is required.", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateResponseWithoutServiceName()
    {
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => '',
            'mobile'       => '01620011019',
            'location_id'  => 4,
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The service name field is required.", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateResponseWithWrongMobileNumber()
    {
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => '01534546',
            'location_id'  => 4,
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateForResponse200WithCountryCodeMobile()
    {
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => '+8801620011019',
            'location_id'  => 4,
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateResponseWithWrongLocationId()
    {
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => '01620011019',
            'location_id'  => 99999999999999999999,
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(404, $data["code"]);
        $this->assertEquals("Location Not Found", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateResponseWithCharactersInMobileNumber()
    {
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => 'qwertyuiop',
            'location_id'  => 4,
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateResponseWithSpecialCharactersInServiceName()
    {
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => '!@#$%^&*',
            'mobile'       => '01620011019',
            'location_id'  => 4,
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateResponseWithWrongServiceNameMobileLocationId()
    {
        $response = $this->post("/v3/customers/info-call", [
            'service_name' => '',
            'mobile'       => '',
            'location_id'  => '',
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals(
            "The service name field is required.The mobile field is required.The location id field is required.",
            $data["message"]
        );
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateForResponseWithGetMethod()
    {
        $response = $this->get("/v3/customers/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => '01620011019',
            'location_id'  => 4,
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals("The GET method is not supported for this route. Supported methods: POST.", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateForResponseWithMobileInParameterAndServiceNameLocationIdInBody()
    {
        $response = $this->post("/v3/customers/info-call?mobile=01620011019", [
            'service_name' => 'Hand maid pizza',
            'location_id'  => 4,
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateForResponseWithServiceNameMobileLocationIdInParameter()
    {
        $response = $this->post(
            "/v3/customers/info-call?service_name=Hand maid pizza&mobile=01620011019&location_id=4"
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
    }
}
