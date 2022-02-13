<?php

namespace Tests\Feature\Digigo\Leave;

use Carbon\Carbon;
use Database\Factories\ApprovalRequestFactory;
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
        $this->assertEquals('Successful', $data['message']);
        $this->getLeaveListAccordingToLeaveType($data);
        $this->returnLeaveListDataInArrayFormat($data);

    }

    private function getLeaveListAccordingToLeaveType($data)
    {
        /**
         *  Leave List Info @return ApprovalRequestFactory
         */
        $this->assertEquals(1, $data['pending_approval_request'][0]['id']);
        $this->assertEquals(1, $data['pending_approval_request'][0]['requestable_id']);
        $this->assertEquals('pending', $data['pending_approval_request'][0]['status']);
        $this->assertEquals(1, $data['pending_approval_request'][0]['approver_id']);
        $this->assertEquals(null, $data['pending_approval_request'][0]['order']);
        $this->assertEquals(1, $data['pending_approval_request'][0]['is_notified']);
        $this->assertEquals(1, $data['pending_approval_request'][0]['created_by']);
        $this->assertEquals(1, $data['approval_requests']['pending_request']);
    }

    private function returnLeaveListDataInArrayFormat($data)
    {
        $this->assertArrayHasKey('id', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('requestable_id', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('status', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('approver_id', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('order', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('is_notified', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('created_by', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('created_by_name', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('updated_by', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('updated_by_name', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('created_at', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('updated_at', $data['pending_approval_request'][0]);
        $this->assertArrayHasKey('pending_request', $data['approval_requests']);
    }
}
