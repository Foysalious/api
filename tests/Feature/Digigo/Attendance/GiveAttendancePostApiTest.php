<?php

namespace Tests\Feature\Digigo\Attendance;

use Carbon\Carbon;
use Database\Factories\AttendanceActionLogFactory;
use Database\Factories\AttendanceFactory;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\BusinessOfficeHours\Model as BusinessOfficeHour;
use Sheba\Dal\BusinessAttendanceTypes\Model as BusinessAttendanceType;
use Sheba\Dal\AttendanceOverrideLogs\AttendanceOverrideLogs;
use Sheba\Dal\AttendanceSummary\AttendanceSummary;


use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class GiveAttendancePostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Attendance::class, AttendanceActionLog::class, BusinessAttendanceType::class, BusinessOfficeHour::class, AttendanceActionLog::class, AttendanceOverrideLogs::class, AttendanceSummary::class]);
        $this->logIn();
        BusinessAttendanceType::factory()->create([
            'business_id' => $this->business->id,
            'attendance_type' => 'remote',
        ]);

        BusinessOfficeHour::factory()->create([
            'business_id' => $this->business->id,
        ]);
    }

    public function testApiReturnSuccessResponseForOnTimeCheckIn()
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

    public function testAPiReturnNotImplementRequestInCheckOutActionIfDeviceIdIsNotMatching()
    {
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkin',
            'device_id' => '6356516637b06549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkout',
            'device_id' => '63565549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(503, $data['code']);

        $this->assertEquals('You can not check-out from this phone. Please use the same phone you checked-in with', $data['message']);
    }
    public function testApiReturnSuccessResponseForOnTimeCheckOut()
    {
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkin',
            'device_id' => '6356516637b06549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkout',
            'device_id' => '6356516637b06549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Good Bye! See you next day.', $data['message']);
    }

    public function testAPiReturnNotImplementResponseForLateCheckIn()
    {
        BusinessOfficeHour::find(1)->update(["start_time" => Carbon::now()->subMinutes(60),]);
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkin',
            'device_id' => '6356516637b06549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(501, $data['code']);
        $this->assertEquals('Oops! You’re late today!', $data['message']);
    }

    public function testAPiReturnNotImplementResponseForEarlyCheckOut()
    {
        BusinessOfficeHour::find(1)->update(["end_time" => Carbon::now()->addMinutes(15)]);
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkin',
            'device_id' => '6356516637b06549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkout',
            'device_id' => '6356516637b06549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(508, $data['code']);
        $this->assertEquals("Good Bye! You're early check-out today!", $data['message']);
    }

    public function testEmployeeAttendanceLogUpdateAfterUserCheckIn()
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
        /**
         *  User Attendance Data @return AttendanceFactory
         */
        $this->assertEquals($this->business_member->id, $attendance->business_member_id);
        $this->assertEquals(Carbon::now()->format('Y') . '-' . Carbon::now()->format('m') . '-' . Carbon::now()->format('d'), $attendance->date);
        $this->assertEquals(Carbon::now()->format('H:i'), Carbon::parse($attendance->checkin_time)->format('H:i'));
        $this->assertEquals('on_time', $attendance->status);

        /**
         *  User Attendance Device, attendance remote info and Ip  Data @return AttendanceActionLogFactory
         */

        $attendance_action_log = AttendanceActionLog::first();
        $this->assertEquals(1, $attendance_action_log->attendance_id);
        $this->assertEquals('checkin', $attendance_action_log->action);
        $this->assertEquals('6356516637b06549', $attendance_action_log->device_id);
        $this->assertEquals('127.0.0.1', $attendance_action_log->ip);
        $this->assertEquals(1, $attendance_action_log->is_remote);
        $this->assertEquals('on_time', $attendance_action_log->status);
    }

    public function testEmployeeAttendanceLogUpdateAfterUserCheckOut()
    {
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkin',
            'device_id' => '6356516637b06549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response = $this->post("/v1/employee/attendances/action", [
            'action' => 'checkout',
            'device_id' => '6356516637b06549',
            'is_in_wifi_area' => 0,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $attendance = Attendance::first();
        /**
         *  User Attendance Data @return AttendanceFactory
         */
        $this->assertEquals($this->business_member->id, $attendance->business_member_id);
        $this->assertEquals(Carbon::now()->format('Y') . '-' . Carbon::now()->format('m') . '-' . Carbon::now()->format('d'), $attendance->date);
        $this->assertEquals(Carbon::now()->format('H:i'), Carbon::parse($attendance->checkin_time)->format('H:i'));
        $this->assertEquals(Carbon::now()->format('H:i'), Carbon::parse($attendance->checkout_time)->format('H:i'));
        $this->assertEquals('', $attendance->status);

        /**
         *  User Attendance Device, attendance remote info and Ip  Data @return AttendanceActionLogFactory
         */

        $attendance_action_log = AttendanceActionLog::first();
        $this->assertEquals(1, $attendance_action_log->attendance_id);
        $this->assertEquals('checkin', $attendance_action_log->action);
        $this->assertEquals('6356516637b06549', $attendance_action_log->device_id);
        $this->assertEquals('127.0.0.1', $attendance_action_log->ip);
        $this->assertEquals(1, $attendance_action_log->is_remote);
        $this->assertEquals('on_time', $attendance_action_log->status);

        $attendance_action_log = AttendanceActionLog::find(2);
        $this->assertEquals(1, $attendance_action_log->attendance_id);
        $this->assertEquals('checkout', $attendance_action_log->action);
        $this->assertEquals('6356516637b06549', $attendance_action_log->device_id);
        $this->assertEquals('127.0.0.1', $attendance_action_log->ip);
        $this->assertEquals(1, $attendance_action_log->is_remote);
        $this->assertEquals('left_timely', $attendance_action_log->status);
    }
}
