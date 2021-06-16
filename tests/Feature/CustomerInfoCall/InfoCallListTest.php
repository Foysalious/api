<?php namespace Tests\Feature\CustomerInfoCall;

use Sheba\Dal\Category\Category;
use Sheba\Dal\InfoCall\InfoCall;
use Sheba\Dal\Service\Service;
use Tests\Feature\FeatureTestCase;

class InfoCallListTest extends FeatureTestCase
{

    private $infocall;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTable(InfoCall::class);

        $this->logIn();

        $this->infocall = factory(InfoCall::class)->create([
            'customer_id' => $this->customer->id,
            'portal_name' => 'customer-app'
        ]);

    }
    public function testInfoCallListCustomerForResponse200()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call", [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals('Ac service', $data["info_call_lists"][0]["service_name"]);
        $this->assertEquals('Open', $data["info_call_lists"][0]["status"]);

    }

    public function testInfoCallListCustomerWithoutValidBearerToken()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call");

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("Authentication token is missing from the request.", $data["message"]);

    }

    public function testInfoCallListCustomerWithInvalidCustomerId()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/19050148413548641353546/info-call", [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(403, $data["code"]);
        $this->assertEquals("You're not authorized to access this user.", $data["message"]);

    }

    public function testInfoCallListCustomerWithInvalidURL()
    {

        //arrange

        //act

        $response = $this->get("/v2/customersdsfdsfefs/19050148413548641353546/info-call", [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals("404 Not Found", $data["message"]);

    }

    public function testInfoCallListCustomerAfterRejectedStatus()
    {

        //arrange
        $this->infocall -> update(["status" => "Rejected"]);

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call", [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals('Rejected', $data["info_call_lists"][0]["status"]);

    }

//    public function testInfoCallListCustomerForRespondfsdfsse200()
//    {
//
//        //arrange
//
//        //act
//        $usf = "yhjgsufkhsfkj";
//
//        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call", [
//            'Authorization' => "Bearer $usf"
//        ]);
//
//        $data = $response->decodeResponseJson();
//        dd($data);
//
//        //assert
//        $this->assertEquals(200, $data["code"]);
//        $this->assertEquals("Successful", $data["message"]);
//        $this->assertEquals('Ac service', $data["info_call_lists"][0]["service_name"]);
//        $this->assertEquals('Open', $data["info_call_lists"][0]["status"]);
//
//    }

    public function testInfoCallListCustomerWithMultipleInfoCall()
    {

        //arrange

        $infoCall2 = factory(InfoCall::class)->create([
            'customer_id' => $this->customer->id,
            'portal_name' => 'customer-app'
        ]);

        $this->infocall -> update(["status" => "Rejected"]);

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call", [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals('Ac service', $data["info_call_lists"][0]["service_name"]);
        $this->assertEquals('Rejected', $data["info_call_lists"][0]["status"]);
        $this->assertEquals('Ac service', $data["info_call_lists"][1]["service_name"]);
        $this->assertEquals('Open', $data["info_call_lists"][1]["status"]);

    }

}