<?php

namespace Tests\Feature\Digigo\Leave;

use Carbon\Carbon;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class EmployeeLeaveDataGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Leave::class, LeaveType::class]);
        $this->logIn();
        LeaveType::factory()->create([
            'business_id' => $this->business->id
        ]);
        Leave::factory()->create([
            'business_member_id' => $this->business_member->id
        ]);
    }

    public function testApiReturnUserAppliedLeaveListIfUserAlreadyApplyForLeave()
    {
        $response = $this->get("/v1/employee/leaves/dates", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnEmployeeLeaveRequestList()
    {
        $response = $this->get("/v1/employee/leaves/dates", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(Carbon::now(), $data['full_day_leaves'][0]);
        $this->assertEquals(Carbon::now()->addDay()->timestamp, $data['full_day_leaves'][1]);
    }

    public function testUserAppliedLeaveListApiFormat()
    {
        $response = $this->get("/v1/employee/leaves/dates", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey(0, $data['full_day_leaves']);
        $this->assertArrayHasKey(1, $data['full_day_leaves']);
    }
}
