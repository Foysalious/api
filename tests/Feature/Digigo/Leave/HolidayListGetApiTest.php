<?php

namespace Tests\Feature\Digigo\Leave;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\BusinessWeekendSettings\BusinessWeekendSettings;
use Sheba\Dal\GovernmentHolidays\Model as GovernmentHoliday;
use Sheba\Dal\BusinessHoliday\Model as BusinessHoliday;
use Sheba\Dal\BusinessWeekend\Model as BusinessWeekend;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class HolidayListGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([GovernmentHoliday::class, BusinessWeekendSettings::class, BusinessWeekend::class, BusinessHoliday::class]);
        $this->logIn();
        GovernmentHoliday::factory()->create();
        BusinessHoliday::factory()->create([
            'business_id' => $this->business->id
        ]);
        BusinessWeekendSettings::factory()->create([
            'business_id' => $this->business->id
        ]);
        BusinessWeekend::factory()->create([
            'business_id' => $this->business->id
        ]);
    }

    public function testApiReturnHolidayListForAParticularBusiness()
    {
        $response = $this->get("/v1/employee/holidays", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getBusinessHolidayAndWeekendList($data);
        $this->assertArrayHasKey('holidays', $data);
        $this->assertArrayHasKey('weekends', $data);
    }

    private function getBusinessHolidayAndWeekendList($data)
    {
        /**
         *  Weekends data from  @return BusinessWeekend
         */
        $weekends = DB::table('business_weekends')->where('business_id', $this->business->id)->get();
        $weekend_dates = [];

        foreach ($weekends as $weekend) {
            $dayOfWeek = $this->dayOfWeek($weekend->weekday_name);
            $startDate = Carbon::now()->startOfMonth()->subMonth()->subDay(1)->next($dayOfWeek);
            $endDate = Carbon::now()->startOfMonth()->addMonths(1)->endOfMonth();

            for ($date = $startDate; $date->lte($endDate); $date->addWeek()) {
                array_push($weekend_dates, $date->format('Y-m-d'));
            }
        }

        /**
         *  Holiday data from  @return BusinessHoliday
         */

        foreach ($data['holidays'] as $item) {
            $this->assertEquals(Carbon::now()->format('Y-m-d'), $item);
        }
        $this->assertEquals($weekend_dates, $data['weekends']);
    }

    private function dayOfWeek($day)
    {
        switch ($day) {
            case "sunday":
                return 0;
                break;
            case "monday":
                return 1;
                break;
            case "tuesday":
                return 2;
                break;
            case "wednesday":
                return 3;
                break;
            case "thursday":
                return 4;
                break;
            case "friday":
                return 5;
                break;
            case "saturday":
                return 6;
                break;
        }
    }
}
