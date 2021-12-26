<?php

namespace Tests\Feature\CustomerInfoCall;

use Tests\Feature\FeatureTestCase;
use Sheba\Dal\InfoCall\InfoCall;
use Throwable;

class InfoCallDetailsTest extends FeatureTestCase
{
    private $infocall;

    public function setUp(): void
    {
        parent::setUp();

        $this->truncateTable(InfoCall::class);

        $this->logIn();

        $this->infocall = InfoCall::factory()->create([
            'customer_id' => $this->customer->id,
            'portal_name' => 'customer-app',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDetailsCustomerForResponse200()
    {
        $response = $this->get(
            "/v2/customers/".$this->customer->id."/info-call/details/".$this->infocall->id."?remember_token=".$this->customer->remember_token
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals($this->infocall->id, $data["details"]["id"]);
        $this->assertEquals('Ac service', $data["details"]["service_name"]);
        $this->assertEquals('Open', $data["details"]["status"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDetailsCustomerWithoutBearerToken()
    {
        $response = $this->get("/v2/customers/".$this->customer->id."/info-call/details/".$this->infocall->id);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("Authentication token is missing from the request.", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDetailsCustomerWithInvalidCustomerId()
    {
        $response = $this->get(
            "/v2/customers/19050148413548641353546/info-call/details/".$this->infocall->id."?remember_token=".$this->customer->remember_token
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals(403, $data["code"]);
        $this->assertEquals("You're not authorized to access this user.", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDetailsCustomerWithInvalidInfoCallId()
    {
        $response = $this->get(
            "/v2/customers/".$this->customer->id."/info-call/details/21102548641354789415?remember_token=".$this->customer->remember_token
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals(500, $data["code"]);
        $this->assertEquals("Something went wrong.", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDetailsCustomerWithInvalidURL()
    {
        $response = $this->get(
            "/v2/customers/".$this->customer->id."/info-call/detailsdghrgdrs/".$this->infocall->id."?remember_token=".$this->customer->remember_token
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals("404 Not Found", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDetailsCustomerAfterRejectedStatus()
    {
        $this->infocall->update(["status" => "Rejected"]);

        $response = $this->get(
            "/v2/customers/".$this->customer->id."/info-call/details/".$this->infocall->id."?remember_token=".$this->customer->remember_token
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals($this->infocall->id, $data["details"]["id"]);
        $this->assertEquals('Ac service', $data["details"]["service_name"]);
        $this->assertEquals('Rejected', $data["details"]["status"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDetailsCustomerWithInvalidBearerToken()
    {
        $response = $this->get(
            "/v2/customers/".$this->customer->id."/info-call/details/".$this->infocall->id."?remember_token=absada"
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals(404, $data["code"]);
        $this->assertEquals("User not found.", $data["message"]);
    }
}
