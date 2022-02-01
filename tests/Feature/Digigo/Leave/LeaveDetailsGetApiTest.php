<?php

namespace Tests\Feature\Digigo\Leave;

use App\Models\BusinessDepartment;
use App\Models\Department;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\BusinessHoliday\Model as BusinessHoliday;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Tests\Feature\FeatureTestCase;
use Sheba\Dal\BusinessMemberLeaveType\Model as BusinessMemberLeaveType;
use Sheba\Dal\BusinessOffice\Model as BusinessOffice;
use Sheba\Dal\BusinessOfficeHours\Model as BusinessOfficeHour;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class LeaveDetailsGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([BusinessDepartment::class, ApprovalSetting::class, Leave::class, LeaveType::class, BusinessHoliday::class, BusinessMemberLeaveType::class, BusinessOffice::class, BusinessOfficeHour::class, ApprovalRequest::class]);
        $this->logIn();
        BusinessDepartment::factory()->create([
            'business_id' => $this->business->id
        ]);
        ApprovalSetting::factory()->create([
            'business_id' => $this->business->id
        ]);
        BusinessHoliday::factory()->create([
            'business_id' => $this->business->id
        ]);
        LeaveType::factory()->create([
            'business_id' => $this->business->id
        ]);

        BusinessMemberLeaveType::factory()->create([
            'business_member_id' => $this->business_member->id,
            'leave_type_id' => '1'
        ]);
        Leave::factory()->create([
            'business_member_id' => $this->business_member->id,
            'leave_type_id' => 1
        ]);
        ApprovalRequest::factory()->create([
            'requestable_id' => '1', //requestable_id is leave id
        ]);

        BusinessOffice::factory()->create();
        BusinessOfficeHour::factory()->create();
    }

    public function testCheckAPiReturnLeaveDetailsWithValidLeaveId()
    {
        $response = $this->get("/v1/employee/leaves/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }
}
