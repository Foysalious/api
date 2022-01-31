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

class ApprovedLeaveRequestPostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([
            LeaveType::class,
            Leave::class,
            ApprovalRequest::class,
        ]);
        DB::table('approval_flow_approvers')->truncate();
        $this->logIn();

        LeaveType::factory()->create([
            'business_id' => $this->business->id
        ]);
        Leave::factory()->create([
            'business_member_id' => $this->business_member->id,
            'leave_type_id' => 1
        ]);
        ApprovalRequest::factory()->create([
            'requestable_id'     =>'1', //requestable_id is leave id
        ]);
    }
    public function testCheckAPiReturnSuccessResponseAfterApprovedLeaveRequest()
    {
        $response = $this->post("/v1/employee/approval-requests/status", [
            'type' => 'Annual Leave',
            'type_id' => '[1]',
            'status' => 'accepted',

        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }

}