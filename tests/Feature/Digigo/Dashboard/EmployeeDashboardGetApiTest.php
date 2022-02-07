<?php

namespace Tests\Feature\Digigo\Dashboard;

use App\Models\BusinessDepartment;
use App\Models\Notification;
use Carbon\Carbon;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\AttendanceOverrideLogs\AttendanceOverrideLogs;
use Sheba\Dal\AttendanceSummary\AttendanceSummary;
use Sheba\Dal\BusinessHoliday\Model as BusinessHoliday;
use Sheba\Dal\BusinessMemberLeaveType\Model as BusinessMemberLeaveType;
use Sheba\Dal\BusinessOffice\Model as BusinessOffice;
use Sheba\Dal\BusinessOfficeHours\Model as BusinessOfficeHour;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveLog\Model as LeaveLog;
use Sheba\Dal\LeaveStatusChangeLog\Model as LeaveStatusChangeLog;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Sheba\Dal\PayrollSetting\PayrollSetting;

/**
 * @author Nawshin Tabassum <nawshin.tabassum@sheba.xyz>
 */
class EmployeeDashboardGetApiTest extends \Tests\Feature\FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([BusinessDepartment::class,
            LeaveLog::class, LeaveStatusChangeLog::class,
            ApprovalSetting::class, Leave::class,
            LeaveType::class,
            BusinessHoliday::class,
            BusinessMemberLeaveType::class,
            BusinessOffice::class,
            BusinessOfficeHour::class,
            ApprovalRequest::class,
            Notification::class,
            Attendance::class,
            AttendanceSummary::class,
            PayrollSetting::class]);

        $this->logIn();

        Notification::factory()->create();
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
        PayrollSetting::factory()->create([
            'business_id' => $this->business->id
        ]);
        BusinessDepartment::factory()->create([
            'business_id' => $this->business->id
        ]);
        ApprovalSetting::factory()->create([
            'business_id' => $this->business->id
        ]);
        BusinessHoliday::factory()->create([
            'business_id' => $this->business->id
        ]);
        LeaveType::factory()->create([
            'business_id' => $this->business->id
        ]);

        BusinessMemberLeaveType::factory()->create([
            'business_member_id' => $this->business_member->id,
            'leave_type_id' => '1'
        ]);
        Leave::factory()->create([
            'business_member_id' => $this->business_member->id,
            'leave_type_id' => 1
        ]);

        LeaveLog::factory()->create([
            'leave_id' => 1
        ]);

        ApprovalRequest::factory()->create([
            'requestable_id' => '1', //requestable_id is leave id
        ]);

        BusinessOffice::factory()->create();
        BusinessOfficeHour::factory()->create();
    }

    public function testDashboardSuccessfulResponseCode()
    {
        $response = $this->get("/v1/employee/dashboard", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }

    public function testDashboardResponseWhenSessionIsExpired()
    {
        $response = $this->get('/v1/employee/dashboard', [
            'Authorization' => "Bearer $this->token" . "jksdghfjgjhyv",
        ]);
        $data = $response->json();

        $this->assertEquals(401, $data['code']);
        $this->assertEquals('Your session has expired. Try Login', $data['message']);
    }

    public function testDashboardDataResponse()
    {
        $response = $this->get('/v1/employee/dashboard', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(1, $data['info']['id']);
        $this->assertEquals(1, $data['info']['business_id']);
        $this->assertEquals(1, $data['info']['business_member_id']);
        $this->assertEquals(1, $data['info']['department_id']);
        $this->assertEquals(1, $data['info']['notification_count']);
        $this->assertEquals(0, $data['info']['attendance']['can_checkin']);
        $this->assertEquals(1, $data['info']['attendance']['can_checkout']);
        $this->assertEquals(Carbon::now()->isoFormat('Do MMMM YYYY'), $data['info']['note_data']['date']);
        $this->assertEquals(0, $data['info']['note_data']['is_note_required']);
        $this->assertEquals(null, $data['info']['note_data']['note_action']);
        $this->assertEquals(true, $data['info']['is_remote_enable']);
        $this->assertEquals(1, $data['info']['is_approval_request_required']);
        $this->assertEquals(1, $data['info']['approval_requests']['pending_request']);
        $this->assertEquals(1, $data['info']['is_profile_complete']);
        $this->assertEquals(null, $data['info']['is_eligible_for_lunch']);
        $this->assertEquals(0, $data['info']['is_sheba_platform']);
        $this->assertEquals(1, $data['info']['is_payroll_enable']);
        $this->assertEquals(0, $data['info']['is_enable_employee_visit']);
        $this->assertEquals(0, $data['info']['pending_visit_count']);
        $this->assertEquals(0, $data['info']['today_visit_count']);
        $this->assertEquals(0, $data['info']['single_visit']);
        $this->assertEquals(0, $data['info']['currently_on_visit']);
        $this->assertEquals(0, $data['info']['is_badge_seen']);
        $this->assertEquals(0, $data['info']['is_manager']);
        $this->assertEquals('Manager', $data['info']['user_profile']['designation']);
    }

    public function testDashboardDataApiResponse()
    {
        $response = $this->get('/v1/employee/dashboard', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('id', $data['info']);
        $this->assertArrayHasKey('business_id', $data['info']);
        $this->assertArrayHasKey('business_member_id', $data['info']);
        $this->assertArrayHasKey('department_id', $data['info']);
        $this->assertArrayHasKey('notification_count', $data['info']);
        $this->assertArrayHasKey('can_checkin', $data['info']['attendance']);
        $this->assertArrayHasKey('can_checkout', $data['info']['attendance']);
        $this->assertArrayHasKey('date', $data['info']['note_data']);
        $this->assertArrayHasKey('is_note_required', $data['info']['note_data']);
        $this->assertArrayHasKey('note_action', $data['info']['note_data']);
        $this->assertArrayHasKey('is_remote_enable', $data['info']);
        $this->assertArrayHasKey('is_approval_request_required', $data['info']);
        $this->assertArrayHasKey('pending_request', $data['info']['approval_requests']);
        $this->assertArrayHasKey('is_profile_complete', $data['info']);
        $this->assertArrayHasKey('is_eligible_for_lunch', $data['info']);
        $this->assertArrayHasKey('is_sheba_platform', $data['info']);
        $this->assertArrayHasKey('is_payroll_enable', $data['info']);
        $this->assertArrayHasKey('is_enable_employee_visit', $data['info']);
        $this->assertArrayHasKey('pending_visit_count', $data['info']);
        $this->assertArrayHasKey('today_visit_count', $data['info']);
        $this->assertArrayHasKey('single_visit', $data['info']);
        $this->assertArrayHasKey('currently_on_visit', $data['info']);
        $this->assertArrayHasKey('is_badge_seen', $data['info']);
        $this->assertArrayHasKey('is_manager', $data['info']);
        $this->assertArrayHasKey('designation', $data['info']['user_profile']);
    }
}
