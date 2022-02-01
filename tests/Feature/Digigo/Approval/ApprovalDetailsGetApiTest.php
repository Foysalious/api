<?php

namespace Tests\Feature\Digigo\Approval;

use Illuminate\Support\Facades\DB;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class ApprovalDetailsGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([
            LeaveType::class,
            Leave::class,
            ApprovalRequest::class,
        ]);
        $this->logIn();

        LeaveType::factory()->create([
            'business_id' => $this->business->id
        ]);
        Leave::factory()->create([
            'business_member_id' => $this->business_member->id,
            'leave_type_id' => 1
        ]);
        ApprovalRequest::factory()->create([
            'requestable_id' => '1', //requestable_id is leave id
        ]);
    }

    public function testApiSuccessfullyReturnEmployeeLeaveApprovalRequestDetailsAccordingToApprovalRequestId()
    {
        $response = $this->get("/v1/employee/approval-requests/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }
}
