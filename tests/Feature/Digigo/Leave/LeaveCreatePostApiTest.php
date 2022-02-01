<?php

namespace Tests\Feature\Digigo\Leave;

use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\ApprovalFlow\Model as ApprovalFlow;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\ApprovalSettingApprover\ApprovalSettingApprover;
use Sheba\Dal\ApprovalSettingModule\ApprovalSettingModule;
use Tests\Feature\FeatureTestCase;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveType\Model as LeaveType;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class LeaveCreatePostApiTest extends FeatureTestCase
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
    }

    public function testApiReturnSuccessResponseAfterCreateLeaveWithValidData()
    {
        $this->createNewUser();
        $response = $this->post("/v1/employee/leaves", [
            'title' => 'Annual Leave',
            'leave_type_id' => 1,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now(),
            'note' => 'Test Leave',
            'substitute' => '2',
            'is_half_day' => 0,
            'half_day_configuration' => 'first_half',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }

    public function createNewUser()
    {
        $this->profile = Profile::factory()->create([
            'mobile' => '+8801620011019',
            'email' => 'khairun@sheba.xyz',
        ]);
        $this->member = Member::factory()->for($this->profile)->create();
        $this->business = Business::factory()->create();
        $this->businessDepartment = BusinessDepartment::factory()->create([
            'business_id' => 1
        ]);
        BusinessRole::factory()->create([
            'business_department_id' => 2
        ]);
        $this->business_member = BusinessMember::factory()->create([
            'business_id' => 1,
            'member_id' => 2,
            'business_role_id' => 1,
            'manager_id' => 1
        ]);
        ApprovalFlow::factory()->create([
            'business_department_id' => 2
        ]);

        DB::table('approval_flow_approvers')->insert(
            [
                'approval_flow_id' => 1,
                'business_member_id' => 2,
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
    }

}