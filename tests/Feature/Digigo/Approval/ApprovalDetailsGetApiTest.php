<?php

namespace Tests\Feature\Digigo\Approval;

use Carbon\Carbon;
use Database\Factories\ApprovalSettingFactory;
use Database\Factories\BusinessDepartmentFactory;
use Database\Factories\BusinessMemberFactory;
use Database\Factories\LeaveFactory;
use Database\Factories\LeaveLogFactory;
use Database\Factories\LeaveRejectionReasonFactory;
use Database\Factories\LeaveStatusChangeLogFactory;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\Expense\Expense;
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
        Expense::factory()->create([
            'member_id' => $this->member->id,
            'business_member_id' => $this->business_member->id
        ]);
        ApprovalRequest::factory()->create([
            'requestable_id' => '1', //requestable_id is leave id
        ]);
    }

    public function testApiSuccessfullyReturnEmployeeLeaveApprovalRequestDetails()
    {
        $response = $this->get("/v1/employee/approval-requests/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getApprovalDetailsFromDatabase($data);
        $this->returnApprovalDetailsDataInArrayFormat($data);
    }

    private function getApprovalDetailsFromDatabase($data)
    {
        /**
         *  id, type, status, leave id @return ApprovalSettingFactory
         */
        $this->assertEquals(1, $data['approval_details']['id']);
        $this->assertEquals('leave', $data['approval_details']['type']);
        $this->assertEquals('pending', $data['approval_details']['status']);
        $this->assertEquals(1, $data['approval_details']['leave']['id']);

        /**
         *  business member id @return LeaveFactory
         */
        $this->assertEquals(1, $data['approval_details']['leave']['business_member_id']);
        /**
         *  employee id, department Id  @return BusinessMemberFactory
         * Department name @retrun BusinessDepartmentFactory
         */
        $this->assertEquals(null, $data['approval_details']['leave']['employee_id']);
        $this->assertEquals('IT', $data['approval_details']['leave']['department']);
        /**
         *  leave title @return LeaveFactory
         */
        $this->assertEquals('Test Leave', $data['approval_details']['leave']['title']);
        /**
         *  leave request @return ApprovalSettingFactory
         */
        $this->assertEquals(Carbon::now()->format('M d') . ' at ' . Carbon::now()->format('h:i a'), $data['approval_details']['leave']['requested_on']); //Feb 02 at 10:01 am
        /**
         *  leave type, total days, leave left, half day configuration,substitute,leave_date,leave status,leave note
         * period,total_leave_days
         *  @return LeaveFactory
         * is_leave_days_exceeded calculate from leaveFactory ($used_leave_days > (int)$total_days)
         */
        $this->assertEquals('Test Leave', $data['approval_details']['leave']['type']);
        $this->assertEquals(null, $data['approval_details']['leave']['total_days']);
        $this->assertEquals(null, $data['approval_details']['leave']['left']);
        $this->assertEquals(0, $data['approval_details']['leave']['is_half_day']);
        $this->assertEquals(null, $data['approval_details']['leave']['half_day_configuration']);
        $this->assertEquals(null, $data['approval_details']['leave']['substitute']);
        $this->assertEquals(false, $data['approval_details']['leave']['is_leave_days_exceeded']);
        $this->assertEquals(Carbon::now()->format('M d, Y') . ' - ' . Carbon::now()->addDay()->format('M d, Y'), $data['approval_details']['leave']['leave_date']);
        $this->assertEquals('pending', $data['approval_details']['leave']['status']);
        $this->assertEquals('Test leave', $data['approval_details']['leave']['note']);
        $this->assertEquals(Carbon::now()->format('M d') . ' - ' . Carbon::now()->addDay()->format('M d'), $data['approval_details']['leave']['period']);
        $this->assertEquals(10, $data['approval_details']['leave']['total_leave_days']);
        /**
         *  super_admin_action_reason request @return LeaveLogFactory
         */
        $this->assertEquals(null, $data['approval_details']['leave']['super_admin_action_reason']);
        $this->assertEquals(1, $data['approval_details']['leave']['business_member_id']);
        /**
         *  status request @return ApprovalSettingFactory
         */
        $this->assertEquals('pending', $data['approval_details']['approvers'][0]['status']);
        /**
         *  reject_reason @return LeaveRejectionReasonFactory
         */
        $this->assertEquals(null, $data['approval_details']['approvers'][0]['reject_reason']);
        /**
         * department @return BusinessDepartmentFactory
         */
        $this->assertEquals(1, $data['approval_details']['department']['department_id']);
        $this->assertEquals('IT', $data['approval_details']['department']['department']);
        /**
         *  designation  @return BusinessMemberFactory
         */
        $this->assertEquals('Manager', $data['approval_details']['department']['designation']);
    }

    private function returnApprovalDetailsDataInArrayFormat($data)
    {
        $this->assertArrayHasKey('id', $data['approval_details']);
        $this->assertArrayHasKey('type', $data['approval_details']);
        $this->assertArrayHasKey('status', $data['approval_details']);
        $this->assertArrayHasKey('created_at', $data['approval_details']);
        $this->assertArrayHasKey('id', $data['approval_details']['leave']);
        $this->assertArrayHasKey('business_member_id', $data['approval_details']['leave']);
        $this->assertArrayHasKey('employee_id', $data['approval_details']['leave']);
        $this->assertArrayHasKey('department', $data['approval_details']['leave']);
        $this->assertArrayHasKey('title', $data['approval_details']['leave']);
        $this->assertArrayHasKey('requested_on', $data['approval_details']['leave']);
        $this->assertArrayHasKey('name', $data['approval_details']['leave']);
        $this->assertArrayHasKey('type', $data['approval_details']['leave']);
        $this->assertArrayHasKey('total_days', $data['approval_details']['leave']);
        $this->assertArrayHasKey('left', $data['approval_details']['leave']);
        $this->assertArrayHasKey('is_half_day', $data['approval_details']['leave']);
        $this->assertArrayHasKey('half_day_configuration', $data['approval_details']['leave']);
        $this->assertArrayHasKey('time', $data['approval_details']['leave']);
        $this->assertArrayHasKey('substitute', $data['approval_details']['leave']);
        $this->assertArrayHasKey('is_leave_days_exceeded', $data['approval_details']['leave']);
        $this->assertArrayHasKey('status', $data['approval_details']['leave']);
        $this->assertArrayHasKey('note', $data['approval_details']['leave']);
        $this->assertArrayHasKey('period', $data['approval_details']['leave']);
        $this->assertArrayHasKey('total_leave_days', $data['approval_details']['leave']);
        $this->assertArrayHasKey('super_admin_action_reason', $data['approval_details']['leave']);
        $this->assertArrayHasKey('business_member_id', $data['approval_details']['leave']);
        $this->assertArrayHasKey('super_admin_action_reason', $data['approval_details']['leave']);
        $this->assertArrayHasKey('name', $data['approval_details']['approvers'][0]);
        $this->assertArrayHasKey('status', $data['approval_details']['approvers'][0]);
        $this->assertArrayHasKey('reject_reason', $data['approval_details']['approvers'][0]);
        $this->assertArrayHasKey('department_id', $data['approval_details']['department']);
        $this->assertArrayHasKey('department', $data['approval_details']['department']);
        $this->assertArrayHasKey('designation', $data['approval_details']['department']);
    }
}
