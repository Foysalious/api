<?php

namespace Tests\Feature\Digigo\Attendance;

use Carbon\Carbon;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\AttendanceOverrideLogs\AttendanceOverrideLogs;
use Sheba\Dal\AttendanceSummary\AttendanceSummary;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class TodaysAttendanceStatusGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Attendance::class, AttendanceSummary::class, AttendanceOverrideLogs::class, AttendanceActionLog::class,]);
        $this->logIn();
        Attendance::factory()->create([
            'business_member_id' => $this->business_member->id,
        ]);
        AttendanceSummary::factory()->create([
            'business_member_id' => $this->business_member->id,
        ]);
        AttendanceActionLog::factory()->create([
            'attendance_id' => 1,
        ]);
        AttendanceOverrideLogs::factory()->create([
            'attendance_id' => 1,
        ]);
    }

    public function testApiReturnEmployeeDailyAttendanceInfo()
    {
        $response = $this->get("/v1/employee/attendances/today", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnEmployeeDailyAttendanceValidData()
    {
        $response = $this->get("/v1/employee/attendances/today", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(0, $data['attendance']['can_checkin']);
        $this->assertEquals(1, $data['attendance']['can_checkout']);
        $this->assertEquals('09:01:17', $data['attendance']['checkin_time']);
        $this->assertEquals('18:01:17', $data['attendance']['checkout_time']);
        $this->assertEquals(0, $data['attendance']['is_geo_required']);
        $this->assertEquals(false, $data['attendance']['is_remote_enable']);

    }

    public function testEmployeeDailyAttendanceApiFormat()
    {
        $response = $this->get("/v1/employee/attendances/today", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('can_checkin', $data['attendance']);
        $this->assertArrayHasKey('can_checkout', $data['attendance']);
        $this->assertArrayHasKey('checkin_time', $data['attendance']);
        $this->assertArrayHasKey('checkout_time', $data['attendance']);
        $this->assertArrayHasKey('is_geo_required', $data['attendance']);
        $this->assertArrayHasKey('is_remote_enable', $data['attendance']);
    }
}

