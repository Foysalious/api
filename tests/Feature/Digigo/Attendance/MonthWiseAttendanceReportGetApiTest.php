<?php

namespace Tests\Feature\Digigo\Attendance;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\AttendanceOverrideLogs\AttendanceOverrideLogs;
use Sheba\Dal\AttendanceSummary\AttendanceSummary;
use Sheba\Dal\BusinessWeekendSettings\BusinessWeekendSettings;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Tests\Feature\FeatureTestCase;
use Sheba\Dal\BusinessWeekend\Model as BusinessWeekend;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class MonthWiseAttendanceReportGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Attendance::class, AttendanceSummary::class, AttendanceOverrideLogs::class, AttendanceActionLog::class, BusinessWeekend::class, LeaveType::class, Leave::class, ApprovalSetting::class, BusinessWeekendSettings::class]);
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
        BusinessWeekend::factory()->create([
            'business_id' => $this->business->id,
        ]);
        BusinessWeekendSettings::factory()->create([
            'business_id' => $this->business->id,
        ]);
        LeaveType::factory()->create([
            'business_id' => $this->business->id,
        ]);
        Leave::factory()->create([
            'business_member_id' => $this->business_member->id,
            'leave_type_id' => 1,
        ]);
        ApprovalSetting::factory()->create([
            'business_id' => $this->business->id,
        ]);
    }

    public function testApiReturnEmployeeAttendanceReportMonthwise()
    {
        $response = $this->get("/v1/employee/attendances?year=2022&month=1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnMonthlyAttendanceReportData()
    {
        $response = $this->get("/v1/employee/attendances?year=2022&month=1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $date_array = [];
        $period = CarbonPeriod::create('2022-01-01', '2022-01-31');
        foreach ($period as $date) {
            array_push($date_array, $date->format('Y-m-d'));
        }
        $this->assertEquals('31', $data['attendance']['statistics']['working_days']);
        $this->assertEquals(0, $data['attendance']['statistics']['present']);
        $this->assertEquals(0, $data['attendance']['statistics']['on_time']);
        $this->assertEquals(0, $data['attendance']['statistics']['late']);
        $this->assertEquals(0, $data['attendance']['statistics']['left_timely']);
        $this->assertEquals(0, $data['attendance']['statistics']['left_early']);
        $this->assertEquals(0, $data['attendance']['statistics']['on_leave']);
        $this->assertEquals(0, $data['attendance']['statistics']['full_day_leave']);
        $this->assertEquals(0, $data['attendance']['statistics']['half_day_leave']);
        foreach ($data['attendance']['daily_breakdown'] as $item) {
            $this->assertIsArray($date_array, "Has Dates");
            $this->assertEquals(null, $item['weekend_or_holiday_tag']);
            $this->assertEquals(0, $item['show_attendance']);
            $this->assertEquals(null, $item['attendance']);
            $this->assertEquals(1, $item['is_absent']);
        }
    }

    public function testAttendanceReportApiFormat()
    {
        $response = $this->get("/v1/employee/attendances?year=2022&month=1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('working_days', $data['attendance']['statistics']);
        $this->assertArrayHasKey('present', $data['attendance']['statistics']);
        $this->assertArrayHasKey('on_time', $data['attendance']['statistics']);
        $this->assertArrayHasKey('late', $data['attendance']['statistics']);
        $this->assertArrayHasKey('left_timely', $data['attendance']['statistics']);
        $this->assertArrayHasKey('left_early', $data['attendance']['statistics']);
        $this->assertArrayHasKey('on_leave', $data['attendance']['statistics']);
        $this->assertArrayHasKey('full_day_leave', $data['attendance']['statistics']);
        $this->assertArrayHasKey('half_day_leave', $data['attendance']['statistics']);
        foreach ($data['attendance']['daily_breakdown'] as $item) {
            $this->assertArrayHasKey('date', $item);
            $this->assertArrayHasKey('weekend_or_holiday_tag', $item);
            $this->assertArrayHasKey('show_attendance', $item);
            $this->assertArrayHasKey('attendance', $item);
            $this->assertArrayHasKey('is_absent', $item);
        }
    }
}

