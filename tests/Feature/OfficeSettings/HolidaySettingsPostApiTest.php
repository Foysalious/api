<?php

namespace Tests\Feature\OfficeSettings;

use Tests\Feature\FeatureTestCase;
use Sheba\Dal\BusinessHoliday\Model as BusinessHoliday;

class HolidaySettingsPostApiTest extends FeatureTestCase
{
    private $business_holidays;

    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
        $this->truncateTables([BusinessHoliday::class]);
    }

    public function testBusinessHolidayPostApiSuccessfulResponseCode()
    {
        $response = $this->post("/v2/businesses/".$this->business->id."/holidays", [
            'end_date'   => '2021-02-15',
            'start_date' => '2021-02-15',
            'title'      => 'Test Holiday',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }

    public function testBusinessHolidayPostApiResponseCodeWithoutEndDate()
    {
        $response = $this->post('/v2/businesses/'.$this->business->id.'/holidays', [
            'start_date' => '2021-02-15',
            'title'      => 'Test Holiday',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data['code']);
        $this->assertEquals('The end date field is required.', $data['message']);
    }

    public function testBusinessHolidayPostApiResponseCodeWithoutStartDate()
    {
        $response = $this->post('/v2/businesses/'.$this->business->id.'/holidays', [
            'start_date' => '',
            'end_date'   => '2021-02-21',
            'title'      => 'Test Holiday',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data['code']);
        $this->assertEquals('The start date field is required.', $data['message']);
    }

    public function testBusinessHolidayPostApiResponseCodeWithoutTitle()
    {
        $response = $this->post('/v2/businesses/'.$this->business->id.'/holidays', [
            'start_date' => '2021-02-21',
            'end_date'   => '2021-02-21',
            'title'      => '',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data['code']);
        $this->assertEquals('The title field is required.', $data['message']);
    }

    public function testBusinessHolidayPostApiResponseWhenSessionIsExpired()
    {
        $response = $this->post('/v2/businesses/'.$this->business->id.'/holidays', [
            'start_date' => '2021-02-21',
            'end_date'   => '2021-02-21',
            'title'      => 'Test Holiday',
        ], [
            'Authorization' => "Bearer $this->token"."jksdghfjgjhyv",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(401, $data['code']);
        $this->assertEquals('Your session has expired. Try Login', $data['message']);
    }

    public function testBusinessHolidayPostApiCount()
    {
        $response = $this->post('/v2/businesses/'.$this->business->id.'/holidays', [
            'start_date' => '2021-02-21',
            'end_date'   => '2021-02-21',
            'title'      => 'Test Holiday',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(1, BusinessHoliday::count());
    }

}
