<?php

namespace Tests\Feature\Digigo\Leave;

use App\Models\BusinessDepartment;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\ApprovalFlow\Model as ApprovalFlow;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\ApprovalSettingApprover\ApprovalSettingApprover;
use Sheba\Dal\ApprovalSettingModule\ApprovalSettingModule;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class LeaveCancelPostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([
            ApprovalFlow::class,
            ApprovalSetting::class,
            ApprovalSettingModule::class,
            ApprovalSettingApprover::class,
            LeaveType::class,
            Leave::class,
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
    }

    public function testApiReturnSuccessResponseAfterCanceledPendingLeaveRequest()
    {
        $response = $this->post("/v1/employee/leaves/1/cancel?%20status=canceled", [
            'status' => 'canceled',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }

    public function testAfterCancelLeaveRequestLeaveDataWillUpdateInDatabase()
    {
        $response = $this->post("/v1/employee/leaves/1/cancel?%20status=canceled", [
            'status' => 'canceled',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $Leave = Leave::first();
        $this->assertEquals('Test Leave', $Leave->title);
        $this->assertEquals($this->business_member->id, $Leave->business_member_id);
        $this->assertEquals(1, $Leave->leave_type_id);
        $this->assertEquals(Carbon::now(), $Leave->start_date);
        $this->assertEquals(Carbon::now()->addDay()->timestamp, $Leave->end_date);
        $this->assertEquals(0, $Leave->is_half_day);
        $this->assertEquals('Test Leave', $Leave->note);
        $this->assertEquals('canceled', $Leave->status);
    }
}
