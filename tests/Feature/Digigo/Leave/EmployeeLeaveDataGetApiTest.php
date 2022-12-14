<?php

namespace Tests\Feature\Digigo\Leave;

use Carbon\Carbon;
use Database\Factories\LeaveFactory;
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
        $this->assertEquals('Successful', $data['message']);
        /**
         * leave date @return LeaveFactory
         */
        $this->assertEquals(Carbon::now()->format('Y-m-d'), $data['full_day_leaves'][0]);
        $this->assertEquals(Carbon::now()->addDay()->format('Y-m-d'), $data['full_day_leaves'][1]);$this->assertArrayHasKey(0, $data['full_day_leaves']);
        $this->assertArrayHasKey(1, $data['full_day_leaves']);
    }
}
