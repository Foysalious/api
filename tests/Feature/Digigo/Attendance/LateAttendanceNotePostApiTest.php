<?php

namespace Tests\Feature\Digigo\Attendance;

use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;
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
            'action' => 'in',
            'note' => 'traffic issue',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        // dd($data);
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }
}
