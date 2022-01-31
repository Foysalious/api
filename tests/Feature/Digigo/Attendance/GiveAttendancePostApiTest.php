<?php

namespace Tests\Feature\Digigo\Attendance;

use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\BusinessOfficeHours\Model as BusinessOfficeHour;
use Sheba\Dal\BusinessAttendanceTypes\Model as BusinessAttendanceType;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */

class GiveAttendancePostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->truncateTables([Attendance::class, AttendanceActionLog::class, BusinessAttendanceType::class, BusinessOfficeHour::class]);
        $this->logIn();
        BusinessAttendanceType::factory()->create([
            'business_id'        =>$this->business->id,
            'attendance_type'    => 'remote',
        ]);

        BusinessOfficeHour::factory()->create([
            'business_id'        =>$this->business->id,
        ]);
    }

    public function testCheckAPiReturnOKResponseForOnTImeCheckIn()
    {
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkin',
            'device_id' => '6356516637b06549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('You have successfully checked-in', $data['message']);
    }

}