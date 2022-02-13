<?php

namespace Tests\Feature\Digigo\Leave;

use App\Models\BusinessDepartment;
use App\Models\Department;
use Carbon\Carbon;
use Database\Factories\BusinessMemberFactory;
use Database\Factories\LeaveFactory;
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
        $Leave = Leave::first();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        /**
         *  Leave updated data @return LeaveFactory
         */
        $this->assertEquals('Test Leave', $Leave->title);
        $this->assertEquals(1, $Leave->leave_type_id);
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i'), Carbon::parse($Leave->start_date)->format('Y-m-d H:i'));
        $this->assertEquals(Carbon::now()->addDay()->format('Y-m-d H:i'), Carbon::parse($Leave->end_date)->format('Y-m-d H:i'));
        $this->assertEquals(0, $Leave->is_half_day);
        $this->assertEquals('Test Leave Update', $Leave->note);
        $this->assertEquals('pending', $Leave->status);
    }
}
