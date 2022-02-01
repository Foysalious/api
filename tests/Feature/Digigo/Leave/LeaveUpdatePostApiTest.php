<?php

namespace Tests\Feature\Digigo\Leave;

use App\Models\BusinessDepartment;
use App\Models\Department;
use Carbon\Carbon;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\BusinessHoliday\Model as BusinessHoliday;
use Sheba\Dal\BusinessMemberLeaveType\Model as BusinessMemberLeaveType;
use Sheba\Dal\BusinessOffice\Model as BusinessOffice;
use Sheba\Dal\BusinessOfficeHours\Model as BusinessOfficeHour;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class LeaveUpdatePostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Department::class, BusinessDepartment::class, ApprovalSetting::class, Leave::class, LeaveType::class, BusinessHoliday::class, BusinessMemberLeaveType::class, BusinessOffice::class, BusinessOfficeHour::class]);
        $this->logIn();
        Department::factory()->create();
        BusinessDepartment::factory()->create([
            'business_id' => $this->business->id
        ]);
        BusinessHoliday::factory()->create([
            'business_id' => $this->business->id
        ]);
        LeaveType::factory()->create([
            'business_id' => $this->business->id
        ]);
        ApprovalSetting::factory()->create([
            'business_id' => $this->business->id
        ]);
        Leave::factory()->create([
            'business_member_id' => $this->business_member->id,
            'leave_type_id' => 1
        ]);
        ApprovalSetting::factory()->create([
            'business_id' => $this->business->id
        ]);

        BusinessMemberLeaveType::factory()->create([
            'business_member_id' => $this->business_member->id,
            'leave_type_id' => '1'
        ]);

        BusinessOffice::factory()->create();
        BusinessOfficeHour::factory()->create();
    }

    public function testApiReturnSuccessResponseAfterUpdateLeaveInformation()
    {
        $response = $this->post("/v1/employee/leaves/1/update", [
            'note' => 'Test Leave Update',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }
}
