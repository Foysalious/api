<?php

namespace Tests\Feature\Digigo\Approval;

use Carbon\Carbon;
use Database\Factories\ApprovalRequestFactory;
use Database\Factories\LeaveFactory;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\ApprovalFlow\Model as ApprovalFlow;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\ApprovalSettingApprover\ApprovalSettingApprover;
use Sheba\Dal\ApprovalSettingModule\ApprovalSettingModule;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveStatusChangeLog\Model as LeaveStatusChangeLog;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class LeaveRejectPostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([
            ApprovalFlow::class,
            ApprovalRequest::class,
            ApprovalSetting::class,
            ApprovalSettingModule::class,
            ApprovalSettingApprover::class,
            LeaveType::class,
            Leave::class,
            LeaveStatusChangeLog::class,
        ]);
        DB::table('approval_flow_approvers')->truncate();
        $this->logIn();
        ApprovalFlow::factory()->create([
            'business_department_id' => 1
        ]);

        DB::table('approval_flow_approvers')->insert(
            [
                'approval_flow_id' => 1,
                'business_member_id' => $this->business_member->id,
            ]
        );
        ApprovalSetting::factory()->create([
            'business_id' => $this->business->id,
        ]);
        ApprovalSettingModule::factory()->create([
            'approval_setting_id' => 1,
        ]);
        ApprovalSettingApprover::factory()->create([
            'approval_setting_id' => 1,
            'type_id' => $this->business_member->id,
        ]);

        LeaveType::factory()->create([
            'business_id' => $this->business->id
        ]);
        Leave::factory()->create([
            'business_member_id' => $this->business_member->id,
            'leave_type_id' => 1
        ]);
        ApprovalRequest::factory()->create([
            'requestable_id' => 1
        ]);
    }

    public function testApiReturnSuccessResponseAfterRejectLeaveRequest()
    {
        $response = $this->post("/v1/employee/approval-requests/status", [
            'type' => 'leave',
            'type_id' => '[1]',
            'status' => 'rejected',
            'reasons' => '["violation_of_leave_policy"]',
            'note' => 'Test Reject Leave',

        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $approvalRequests = ApprovalRequest::first();
        $leave = Leave::first();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        /**
         *  store @return ApprovalRequestFactory
         */
        $this->assertEquals(1, $approvalRequests->requestable_id);
        $this->assertEquals('rejected', $approvalRequests->status);
        $this->assertEquals(1, $approvalRequests->approver_id);
        /**
         *  Store @return LeaveFactory
         */
        $this->assertEquals($this->business_member->id, $leave->business_member_id);
        $this->assertEquals('rejected', $leave->status);
    }
}
