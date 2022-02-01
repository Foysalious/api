<?php

namespace Tests\Feature\Digigo\Leave;

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
}
