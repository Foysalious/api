<?php

namespace Tests\Feature\Digigo\Leave;

use Tests\Feature\FeatureTestCase;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Sheba\Dal\Leave\Model as Leave;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class LeaveListGetApiTest extends FeatureTestCase
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

    public function testApiReturnUserLeaveListAccordingLeaveTypeId()
    {
        $response = $this->get("/v1/employee/leaves?type=1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }
}
