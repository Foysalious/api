<?php

namespace Tests\Feature\Digigo\Leave;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sheba\Dal\BusinessHoliday\Model as BusinessHoliday;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class HolidayListGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([BusinessHoliday::class]);
        $this->logIn();
        BusinessHoliday::factory()->create([
            'business_id' => $this->business->id
        ]);
    }

    public function testApiReturnHolidayList()
    {
        $response = $this->get("/v1/employee/holidays", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

 /*   public function testApiReturnValidLeaveTypeList()
    {
        $response = $this->get("/v1/employee/holidays", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();

        foreach ($weekend as $date) {
            array_push($date_array, $date->format('Y-m-d'));
        }
        foreach ($data['weekends'] as $item) {
            $this->assertEquals(1,$item['id']);
            $this->assertEquals('Test Leave',$item['title']);
            $this->assertEquals(20,$item['total_days']);
            $this->assertEquals(0,$item['is_half_day_enable']);
            $this->assertEquals(20,$item['available_days']);
        }
    }

    public function testEmployeeLevaeTypeListApiFormat()
    {
        $response = $this->get("/v1/employee/holidays", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        foreach ($data['leave_types'] as $item) {
            $this->assertArrayHasKey('id',$item);
            $this->assertArrayHasKey('title',$item);
            $this->assertArrayHasKey('total_days',$item);
            $this->assertArrayHasKey('is_half_day_enable',$item);
            $this->assertArrayHasKey('available_days',$item);
        }
        $this->assertArrayHasKey('half_day_configuration', $data);
        $this->assertArrayHasKey('start_date', $data['fiscal_year']);
        $this->assertArrayHasKey('end_date', $data['fiscal_year']);
    }*/
}
