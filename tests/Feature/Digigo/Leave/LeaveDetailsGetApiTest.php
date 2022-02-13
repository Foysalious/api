<?php

namespace Tests\Feature\Digigo\Leave;

use App\Models\BusinessDepartment;
use App\Models\Career;
use Carbon\Carbon;
use Database\Factories\ApprovalRequestFactory;
use Database\Factories\BusinessMemberFactory;
use Database\Factories\LeaveFactory;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\BusinessHoliday\Model as BusinessHoliday;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Tests\Feature\FeatureTestCase;
use Sheba\Dal\BusinessMemberLeaveType\Model as BusinessMemberLeaveType;
use Sheba\Dal\BusinessOffice\Model as BusinessOffice;
use Sheba\Dal\BusinessOfficeHours\Model as BusinessOfficeHour;
use Sheba\Dal\LeaveLog\Model as LeaveLog;
use Sheba\Dal\LeaveStatusChangeLog\Model as LeaveStatusChangeLog;


/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class LeaveDetailsGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([BusinessDepartment::class, LeaveLog::class, LeaveStatusChangeLog::class, ApprovalSetting::class, Leave::class, LeaveType::class, BusinessHoliday::class, BusinessMemberLeaveType::class, BusinessOffice::class, BusinessOfficeHour::class, ApprovalRequest::class]);
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

        LeaveLog::factory()->create([
            'leave_id' => 1
        ]);

        ApprovalRequest::factory()->create([
            'requestable_id' => '1', //requestable_id is leave id
        ]);

        BusinessOffice::factory()->create();
        BusinessOfficeHour::factory()->create();
    }

    public function testApiReturnLeaveDetails()
    {
        $response = $this->get("/v1/employee/leaves/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->geLeaveDetailsFromDatabase($data);
        $this->returnLeaveDetailDataInArrayFormat($data);
    }

    private function geLeaveDetailsFromDatabase($data)
    {
        /**
         *  Leave info @return LeaveFactory
         */
        $this->assertEquals('Test Leave', $data['leave']['title']);
        $this->assertEquals('Test Leave', $data['leave']['leave_type']);
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i'), Carbon::parse($data['leave']['start_date']['date'])->format('Y-m-d H:i'));
        $this->assertEquals(3, $data['leave']['start_date']['timezone_type']);
        $this->assertEquals('Asia/Dhaka', $data['leave']['start_date']['timezone']);
        $this->assertEquals(Carbon::now()->addDay()->format('Y-m-d H:i:s'), $data['leave']['end_date']['date']);
        $this->assertEquals(3, $data['leave']['end_date']['timezone_type']);
        $this->assertEquals('Asia/Dhaka', $data['leave']['end_date']['timezone']);
        $this->assertEquals(null, $data['leave']['total_days']);
        $this->assertEquals(0, $data['leave']['is_half_day']);
        $this->assertEquals(0, $data['leave']['half_day_configuration']);
        $this->assertEquals(Carbon::now()->addMinutes(15)->format('h:i') . "-" . Carbon::now()->subMinutes(15)->format('h:i'), $data['leave']['time']);
        $this->assertEquals('pending', $data['leave']['status']);
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i'), Carbon::parse($data['leave']['requested_on']['date'])->format('Y-m-d H:i'));
        $this->assertEquals(3, $data['leave']['requested_on']['timezone_type']);
        $this->assertEquals('Asia/Dhaka', $data['leave']['requested_on']['timezone']);
        $this->assertEquals('Test leave', $data['leave']['note']);
        $this->assertEquals(null, $data['leave']['substitute']);
        /**
         *  approvers info @return ApprovalRequestFactory
         */
        $this->assertEquals('pending', $data['leave']['approvers'][0]['status']);
        $this->assertEquals(1, $data['leave']['approver_count']);
        $this->assertEquals('Super Admin changed this leave status from Pending to Accepted', $data['leave']['leave_log_details'][0]['log']);
        $this->assertEquals(Carbon::now()->format('h:i A') . " - " . Carbon::now()->format('d M, Y'), $data['leave']['leave_log_details'][0]['created_at']);
        /**
         *  is_substitute_required when memeber is "Manager". this info   @return BusinessMemberFactory
         */
        $this->assertEquals(1, $data['leave']['is_substitute_required']);
        /**
         *  is_cancelable_request depends on leave start dates and end dates  @return LeaveFactory
         */
        $this->assertEquals(0, $data['leave']['is_cancelable_request']);
    }

    private function returnLeaveDetailDataInArrayFormat($data)
    {
        $this->assertArrayHasKey('title', $data['leave']);
        $this->assertArrayHasKey('leave_type', $data['leave']);
        $this->assertArrayHasKey('date', $data['leave']['start_date']);
        $this->assertArrayHasKey('timezone_type', $data['leave']['start_date']);
        $this->assertArrayHasKey('timezone', $data['leave']['start_date']);
        $this->assertArrayHasKey('date', $data['leave']['end_date']);
        $this->assertArrayHasKey('timezone_type', $data['leave']['end_date']);
        $this->assertArrayHasKey('timezone', $data['leave']['end_date']);
        $this->assertArrayHasKey('total_days', $data['leave']);
        $this->assertArrayHasKey('is_half_day', $data['leave']);
        $this->assertArrayHasKey('half_day_configuration', $data['leave']);
        $this->assertArrayHasKey('time', $data['leave']);
        $this->assertArrayHasKey('status', $data['leave']);
        $this->assertArrayHasKey('date', $data['leave']['requested_on']);
        $this->assertArrayHasKey('timezone_type', $data['leave']['requested_on']);
        $this->assertArrayHasKey('timezone', $data['leave']['requested_on']);
        $this->assertArrayHasKey('note', $data['leave']);
        $this->assertArrayHasKey('substitute', $data['leave']);
        $this->assertArrayHasKey('status', $data['leave']['approvers'][0]);
        $this->assertArrayHasKey('approver_count', $data['leave']);
        $this->assertArrayHasKey('log', $data['leave']['leave_log_details'][0]);
        $this->assertArrayHasKey('created_at', $data['leave']['leave_log_details'][0]);
        $this->assertArrayHasKey('is_substitute_required', $data['leave']);
        $this->assertArrayHasKey('is_cancelable_request', $data['leave']);
    }
}
