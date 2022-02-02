<?php

namespace Tests\Feature\Digigo\Attendance;

use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class LateAttendanceNotePostApiTest extends FeatureTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    public function testApiReturnOkResponseForSuccessfullySubmitNoteForLateIn()
    {
        $response = $this->post("/v1/employee/attendances/note", [
            'action' => 'checkin',
            'note' => 'traffic issue',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }

    public function testLeaveNoteWillStoreInAttendanceActionLogDbWhenUserActionLateCheckIn()
    {
        $response = $this->post("/v1/employee/attendances/note", [
            'action' => 'checkin',
            'note' => 'traffic issue',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $attendance_action_logs = AttendanceActionLog::first();
        $this->assertEquals(1, $attendance_action_logs->attendance_id);
        $this->assertEquals('checkin', $attendance_action_logs->action);
        $this->assertEquals('on_time', $attendance_action_logs->status);
        $this->assertEquals('traffic issue', $attendance_action_logs->note);
    }
}
