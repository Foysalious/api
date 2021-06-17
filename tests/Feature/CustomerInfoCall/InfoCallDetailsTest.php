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

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/details/" . $this->infocall->id . "", [
            'Authorization' => "Bearer $this->token"
        ]);

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

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/details/" . $this->infocall->id . "");

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("Authentication token is missing from the request.", $data["message"]);

    }

    public function testInfoCallDetailsCustomerWithInvalidCustomerId()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/19050148413548641353546/info-call/details/" . $this->infocall->id . "", [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(403, $data["code"]);
        $this->assertEquals("You're not authorized to access this user.", $data["message"]);

    }

//    public function testInfoCallDetailsCustomerWithInfoCallOrderIdOfOtherCustomer()
//    {
//
//        //arrange
//
//        $oldCustomerId = $this->infocall->id;
//        $this->logInWithMobileNEmail("+8801715559988");
//        dd($this->customer->id);
//
//        //act
//
//
//
//        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/details/" . $this->infocall->id . "", [
//            'Authorization' => "Bearer $this->token"
//        ]);
//
//        $data = $response->decodeResponseJson();
//
//        //assert
//        $this->assertEquals(403, $data["code"]);
//        $this->assertEquals("You're not authorized to access this user.", $data["message"]);
//
//    }

    public function testInfoCallDetailsCustomerWithInvalidInfoCallId()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/details/21102548641354789415", [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(500, $data["code"]);
        $this->assertEquals("Something went wrong.", $data["message"]);

    }

    public function testInfoCallDetailsCustomerWithInvalidURL()
    {

        //arrange

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/detailsdghrgdrs/" . $this->infocall->id . "", [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals("404 Not Found", $data["message"]);

    }

    public function testInfoCallDetailsCustomerAfterRejectedStatus()
    {

        //arrange
        $this->infocall -> update(["status" => "Rejected"]);

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/details/" . $this->infocall->id . "", [
            'Authorization' => "Bearer $this->token"
        ]);

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
        $usf = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiS2F6aSBGYWhkIFpha3dhbiIsImltYWdlIjoiaHR0cHM6Ly9zMy5hcC1zb3V0aC0xLmFtYXpvbmF3cy5jb20vY2RuLXNoZWJhZGV2L2ltYWdlcy9yZXNvdXJjZXMvYXZhdGFyLzE2MjI1MjA3NDNfa2F6aWZhaGR6YWt3YW4uanBnIiwicHJvZmlsZSI6eyJpZCI6MjYyNTM1LCJuYW1lIjoiS2F6aSBGYWhkIFpha3dhbiIsImVtYWlsX3ZlcmlmaWVkIjowfSwiY3VzdG9tZXIiOnsiaWQiOjE5MDUwMX0sInJlc291cmNlIjp7ImlkIjo0NjMzMSwicGFydG5lciI6eyJpZCI6MjE2NzA0LCJuYW1lIjoiIiwic3ViX2RvbWFpbiI6InNlcnZpY2luZy1iZCIsImxvZ28iOiJodHRwczovL3MzLmFwLXNvdXRoLTEuYW1hem9uYXdzLmNvbS9jZG4tc2hlYmFkZXYvaW1hZ2VzL3BhcnRuZXJzL2xvZ29zLzE2MjI0NDM4ODBfc2VydmljaW5nYmQucG5nIiwiaXNfbWFuYWdlciI6dHJ1ZX19LCJwYXJ0bmVyIjpudWxsLCJtZW1iZXIiOm51bGwsImJ1c2luZXNzX21lbWJlciI6bnVsbCwiYWZmaWxpYXRlIjpudWxsLCJsb2dpc3RpY191c2VyIjpudWxsLCJiYW5rX3VzZXIiOm51bGwsInN0cmF0ZWdpY19wYXJ0bmVyX21lbWJlciI6bnVsbCwiYXZhdGFyIjp7InR5cGUiOiJjdXN0b21lciIsInR5cGVfaWQiOjE5MDUwMX0sImV4cCI6MTYyNDM0ODg2OSwic3ViIjoyNjI1MzUsImlzcyI6Imh0dHA6Ly9hY2NvdW50cy5kZXYtc2hlYmEueHl6L2FwaS92My90b2tlbi9nZW5lcmF0ZSIsImlhdCI6MTYyMzc0NDA3MCwibmJmIjoxNjIzNzQ0MDcwLCJqdGkiOiJGcEJvT0V2NGNnekhweThWIn0.gWbCfYkrSfdIdv8GMRz4gFZXDRdIYR5XA_hR3CRMdn8";

        //act

        $response = $this->get("/v2/customers/" . $this->customer->id . "/info-call/details/" . $this->infocall->id . "", [
            'Authorization' => "Bearer $usf"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(401, $data["code"]);
        $this->assertEquals("Your session has expired. Try Login", $data["message"]);

    }

}