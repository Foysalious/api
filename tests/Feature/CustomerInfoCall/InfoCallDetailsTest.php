<?php namespace Tests\Feature\CustomerInfoCall;

use Tests\Feature\FeatureTestCase;
use Sheba\Dal\InfoCall\InfoCall;

class InfoCallDetailsTest extends FeatureTestCase
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

    public function testInfoCallDetailsCustomerForResponse200()
    {

        //arrange


        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/details/" . $this->infocall->id . "?remember_token=" .$this->customer->remember_token);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals($this->infocall->id, $data["details"]["id"]);
        $this->assertEquals('Ac service', $data["details"]["service_name"]);
        $this->assertEquals('Open', $data["details"]["status"]);

    }

    public function testInfoCallDetailsCustomerWithoutBearerToken()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/details/" . $this->infocall->id);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("Authentication token is missing from the request.", $data["message"]);

    }

    public function testInfoCallDetailsCustomerWithInvalidCustomerId()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/19050148413548641353546/info-call/details/" . $this->infocall->id . "?remember_token=" . $this->customer->remember_token);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(403, $data["code"]);
        $this->assertEquals("You're not authorized to access this user.", $data["message"]);

    }

    public function testInfoCallDetailsCustomerWithInvalidInfoCallId()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/details/21102548641354789415?remember_token=" . $this->customer->remember_token);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(500, $data["code"]);
        $this->assertEquals("Something went wrong.", $data["message"]);

    }

    public function testInfoCallDetailsCustomerWithInvalidURL()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/detailsdghrgdrs/" . $this->infocall->id . "?remember_token=" . $this->customer->remember_token);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals("404 Not Found", $data["message"]);

    }

    public function testInfoCallDetailsCustomerAfterRejectedStatus()
    {

        //arrange
        $this->infocall -> update(["status" => "Rejected"]);

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/details/" . $this->infocall->id . "?remember_token=" . $this->customer->remember_token);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals($this->infocall->id, $data["details"]["id"]);
        $this->assertEquals('Ac service', $data["details"]["service_name"]);
        $this->assertEquals('Rejected', $data["details"]["status"]);

    }

    public function testInfoCallDetailsCustomerWithInvalidBearerToken()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/details/" . $this->infocall->id . "?remember_token=absada");

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals("User not found.", $data["message"]);

    }

}