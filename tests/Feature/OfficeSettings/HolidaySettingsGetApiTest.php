<?php

namespace Tests\Feature\OfficeSettings;

use App\Models\Update;
use Carbon\Carbon;
use Tests\Feature\FeatureTestCase;
use Sheba\Dal\BusinessHoliday\Model as BusinessHoliday;

/**
 * @author Nawshin Tabassum <nawshin.tabassum@sheba.xyz>
 */
class HolidaySettingsGetApiTest extends FeatureTestCase
{
    /** @var BusinessHoliday $business_holidays */
    private $business_holidays;

    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
        $this->truncateTables([BusinessHoliday::class]);

        $this->business_holidays = BusinessHoliday::factory()->create([
            'business_id' => $this->business->id,
        ]);
    }

    public function testSuccessfulResponseCode()
    {
        $response = $this->get("/v2/businesses/".$this->business->id."/holidays", [
            'Authorization' => "Bearer $this->token",

        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }

    public function testBusinessHolidayResponseWhenBusinessIsNotAvailable()
    {
        $response = $this->get('/v2/businesses/2/holidays', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(404, $data['code']);
        $this->assertEquals('Business not found.', $data['message']);
    }

    public function testBusinessHolidayResponseWhenSessionIsExpired()
    {
        $response = $this->get('/v2/businesses/2/holidays', [
            'Authorization' => "Bearer $this->token"."jksdghfjgjhyv",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(401, $data['code']);
        $this->assertEquals('Your session has expired. Try Login', $data['message']);
    }

    public function testBusinessHolidayDataResponse()
    {
        $response = $this->get('/v2/businesses/1/holidays', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(1, $data['business_holidays'][0]['id']);
        $this->assertEquals('21/02/2021', $data['business_holidays'][0]['start_date']);
        //$this->assertEquals('21/02/2021',$data['business_holidays'][0]['end_date']);
        $this->assertEquals('23/02/2021', $data['business_holidays'][0]['end_date']);
        //$this->assertEquals('0',$data['business_holidays'][0]['day_difference']);
        $this->assertEquals('2', $data['business_holidays'][0]['day_difference']);
        //$this->assertEquals('21 February, 2021',$data['business_holidays'][0]['date']);
        $this->assertEquals('21 February, 2021 - 23 February, 2021', $data['business_holidays'][0]['date']);
        //$this->assertEquals('1 day',$data['business_holidays'][0]['total_days']);
        $this->assertEquals('3 days', $data['business_holidays'][0]['total_days']);
        $this->assertEquals('Test Holiday', $data['business_holidays'][0]['name']);
        //$this->assertEquals('Test Holidays',$data['business_holidays'][0]['name']);
    }

    public function testBusinessHolidayUpdateStartDateDataResponse()
    {
        $start_date = BusinessHoliday::find(1);
        $start_date->update(["start_date" => Carbon::parse('2021-03-26')]);
        $response = $this->get('/v2/businesses/1/holidays', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals('26/03/2021', $data['business_holidays'][0]['start_date']);
    }

    public function testBusinessHolidayUpdateEndDateDataResponse()
    {
        $end_date = BusinessHoliday::find(1);
        $end_date->update(["end_date" => Carbon::parse('2021-03-27')]);
        $response = $this->get('/v2/businesses/1/holidays', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals('27/03/2021', $data['business_holidays'][0]['end_date']);
    }

    public function testBusinessHolidayUpdateDayDifferenceDataResponse()
    {
        $start_date = BusinessHoliday::find(1);
        $start_date->update(["start_date" => Carbon::parse('2021-03-26')]);
        $end_date = BusinessHoliday::find(1);
        $end_date->update(["end_date" => Carbon::parse('2021-03-27')]);
        $response = $this->get('/v2/businesses/1/holidays', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals('1', $data['business_holidays'][0]['day_difference']);
    }

    public function testBusinessHolidayUpdateTitleDataResponse()
    {
        $title = BusinessHoliday::find(1);
        $title->update(["title" => 'Independence Day']);
        $response = $this->get('/v2/businesses/1/holidays', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals('Independence Day', $data['business_holidays'][0]['name']);
    }

    public function testBusinessHolidayCount()
    {
        $response = $this->get('/v2/businesses/1/holidays', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $business_holidays = collect($data['business_holidays']);
        $this->assertEquals(1, $business_holidays->count());
    }
}
