<?php

namespace Tests\Feature\Digigo\Attendance;

use Carbon\Carbon;
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
        parent::setUp();
        $this->truncateTables([Attendance::class, AttendanceActionLog::class, BusinessAttendanceType::class, BusinessOfficeHour::class]);
        $this->logIn();
        BusinessAttendanceType::factory()->create([
            'business_id' => $this->business->id,
            'attendance_type' => 'remote',
        ]);

        BusinessOfficeHour::factory()->create([
            'business_id' => $this->business->id,
        ]);
    }

    public function testApiReturnOKResponseForOnTImeIn()
    {
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkin',
            'device_id' => '6356516637b06549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('You have successfully checked-in', $data['message']);
    }

    public function testLeaveRequestStatusWillUpdateAfterApprovedLeaveRequest()
    {
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkin',
            'device_id' => '6356516637b06549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $attendance = Attendance::first();
        $this->assertEquals($this->business_member->id, $attendance->business_member_id);
        $this->assertEquals(Carbon::now()->format('Y').'-'. Carbon::now()->format('m').'-'. Carbon::now()->format('d'), $attendance->date);
        $this->assertEquals(Carbon::now()->format('H:i:s'), $attendance->checkin_time);
        $this->assertEquals('on_time', $attendance->status);
    }
}
