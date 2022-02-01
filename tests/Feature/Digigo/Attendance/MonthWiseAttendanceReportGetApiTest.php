<?php

namespace Tests\Feature\Digigo\Attendance;

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

    public function testCheckAPiReturnEmployeeAttendanceReportMonthwise()
    {
        $response = $this->get("/v1/employee/attendances?year=2022&month=1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }
}
