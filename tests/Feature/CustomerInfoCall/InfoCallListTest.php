<?php namespace Tests\Feature\CustomerInfoCall;

use App\Models\Order;
use Sheba\Dal\InfoCall\InfoCall;
use Tests\Feature\FeatureTestCase;

class InfoCallListTest extends FeatureTestCase
{

    private $infocall;
    private $infocall2;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTables([InfoCall::class, Order::class]);

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

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call?remember_token=" . $this->customer->remember_token);

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

        $response = $this->get("/v2/customers/19050148413548641353546/info-call?remember_token=" . $this->customer->remember_token);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(403, $data["code"]);
        $this->assertEquals("You're not authorized to access this user.", $data["message"]);

    }

    public function testInfoCallListCustomerWithInvalidURL()
    {

        //arrange

        //act

        $response = $this->get("/v2/customersdsfdsfefs/" . $this->customer->id . "/info-call?remember_token=" . $this->customer->remember_token);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals("404 Not Found", $data["message"]);

    }

    public function testInfoCallListCustomerAfterRejectedStatus()
    {

        //arrange
        $this->infocall -> update(["status" => "Rejected"]);

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call?remember_token=" . $this->customer->remember_token);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals('Rejected', $data["info_call_lists"][0]["status"]);

    }

    public function testInfoCallListCustomerWithInvalidBearerToken()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call?remember_token=fhaf");

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals("User not found.", $data["message"]);

    }

    public function testInfoCallListCustomerWithMultipleInfoCall()
    {

        //arrange

        $this->infocall2 = factory(InfoCall::class)->create([
            'customer_id' => $this->customer->id,
            'portal_name' => 'customer-app'
        ]);

        $this->infocall -> update(["status" => "Rejected"]);

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call?remember_token=" . $this->customer->remember_token);

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