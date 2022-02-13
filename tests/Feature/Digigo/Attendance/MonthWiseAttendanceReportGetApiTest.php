<?php

namespace Tests\Feature\Digigo\Attendance;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Database\Factories\AttendanceActionLogFactory;
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
        $this->assertEquals('Successful', $data['message']);
        /**
         *  Attendance Info @return AttendanceActionLogFactory
         */
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-01', null, 0, null, 1, 0);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-02', null, 0, null, 1, 1);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-03', null, 0, null, 1, 2);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-04', null, 0, null, 1, 3);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-05', null, 0, null, 1, 4);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-06', null, 0, null, 1, 5);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-07', null, 0, null, 1, 6);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-08', null, 0, null, 1, 7);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-09', null, 0, null, 1, 8);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-10', null, 0, null, 1, 9);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-11', null, 0, null, 1, 10);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-12', null, 0, null, 1, 11);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-13', null, 0, null, 1, 12);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-14', null, 0, null, 1, 13);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-15', null, 0, null, 1, 14);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-16', null, 0, null, 1, 15);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-17', null, 0, null, 1, 16);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-18', null, 0, null, 1, 17);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-19', null, 0, null, 1, 18);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-20', null, 0, null, 1, 19);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-21', null, 0, null, 1, 20);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-22', null, 0, null, 1, 21);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-23', null, 0, null, 1, 22);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-24', null, 0, null, 1, 23);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-25', null, 0, null, 1, 24);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-26', null, 0, null, 1, 25);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-27', null, 0, null, 1, 26);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-28', null, 0, null, 1, 27);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-29', null, 0, null, 1, 28);
        $this->ReturnMonthlyAttendanceReportData($data, '2022-01-30', null, 0, null, 1, 29);
        $this->ReturnAttendanceReportApiFormat($data);
    }

    private function ReturnMonthlyAttendanceReportData($data, $date, $weekend_or_holiday_tag, $show_attendance, $attendance, $is_absent, $index)

    {
        /**
         *  Attendance Info @return AttendanceActionLogFactory
         */
        $this->assertEquals('31', $data['attendance']['statistics']['working_days']);
        $this->assertEquals(0, $data['attendance']['statistics']['present']);
        $this->assertEquals(0, $data['attendance']['statistics']['on_time']);
        $this->assertEquals(0, $data['attendance']['statistics']['late']);
        $this->assertEquals(0, $data['attendance']['statistics']['left_timely']);
        $this->assertEquals(0, $data['attendance']['statistics']['left_early']);
        $this->assertEquals(0, $data['attendance']['statistics']['on_leave']);
        $this->assertEquals(0, $data['attendance']['statistics']['full_day_leave']);
        $this->assertEquals(0, $data['attendance']['statistics']['half_day_leave']);
        $this->assertEquals(31, $data['attendance']['statistics']['absent']);
        $this->assertEquals($date, $data['attendance']['daily_breakdown'][$index]['date']);
        $this->assertEquals($weekend_or_holiday_tag, $data['attendance']['daily_breakdown'][$index]['weekend_or_holiday_tag']);
        $this->assertEquals($show_attendance, $data['attendance']['daily_breakdown'][$index]['show_attendance']);
        $this->assertEquals($attendance, $data['attendance']['daily_breakdown'][$index]['attendance']);
        $this->assertEquals($is_absent, $data['attendance']['daily_breakdown'][$index]['is_absent']);
    }

    private function ReturnAttendanceReportApiFormat($data)
    {
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

