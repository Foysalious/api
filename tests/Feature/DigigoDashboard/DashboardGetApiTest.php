<?php namespace Tests\Feature\DigigoDashboard;


class DashboardGetApiTest extends \Tests\Feature\FeatureTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->logIn();
    }

    public function testDashboardSuccessfulResponseCode()
    {
        $response = $this->get("/v1/employee/dashboard" , [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);

        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }

    public function testDashboardResponseWhenSessionIsExpired()
    {
        $response = $this->get('/v1/employee/dashboard', [
            'Authorization' => "Bearer $this->token" . "jksdghfjgjhyv"
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(401, $data['code']);
        $this->assertEquals('Your session has expired. Try Login', $data['message']);
    }

    public function testDashboardDataResponse()
    {
        $response = $this->get('/v1/employee/dashboard', [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();

       //dd($data);
        $this->assertEquals(1, $data['info']['id']);
        $this->assertEquals(0, $data['info']['notification_count']);
        $this->assertEquals(1, $data['info']['attendance']['can_checkin']);
        $this->assertEquals(0, $data['info']['attendance']['can_checkout']);
        $this->assertEquals(0, $data['info']['attendance']['is_note_required']);
        $this->assertEquals(0, $data['info']['is_approval_request_required']);
        $this->assertEquals(0, $data['info']['approval_requests']['pending_request']);
        $this->assertEquals(0, $data['info']['is_profile_complete']);
        $this->assertEquals(null, $data['info']['is_eligible_for_lunch']);
    }

   /* public function testDashboardUpdateCanCheckInDataResponse()
    {
        $can_checkin
    }*/
}