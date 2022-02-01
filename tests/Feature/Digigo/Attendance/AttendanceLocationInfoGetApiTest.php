<?php

namespace Tests\Feature\Digigo\Attendance;

use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\AttendanceOverrideLogs\AttendanceOverrideLogs;
use Sheba\Dal\AttendanceSummary\AttendanceSummary;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class AttendanceLocationInfoGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Attendance::class, AttendanceSummary::class, AttendanceOverrideLogs::class, AttendanceActionLog::class]);
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

    public function testApiReturnEmployeeLocationAddressAccordingToLatLng()
    {
        $response = $this->get("/v1/employee/attendances/info?lat=23.7980928&lng=90.4047646", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }
}
