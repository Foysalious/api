<?php

namespace Tests\Feature\Digigo\Approval;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class ApprovalListGetApiTest extends FeatureTestCase
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
            'requestable_id' => '1', //requestable_id is leave id
        ]);
    }

    public function testApiSuccessfullyReturnApprovalListAccordingToLimitParams()
    {
        $response = $this->get("/v1/employee/approval-requests?type=&limit=1&offset=0", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnValidApprovalListForSuccessResponse()
    {
        $response = $this->get("/v1/employee/approval-requests?type=&limit=1&offset=0", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(1, $data['request_lists'][0]['id']);
        $this->assertEquals('leave', $data['request_lists'][0]['type']);
        $this->assertEquals('pending', $data['request_lists'][0]['status']);
        $this->assertEquals(1, $data['request_lists'][0]['leave']['id']);
        $this->assertEquals(1, $data['request_lists'][0]['leave']['business_member_id']);
        $this->assertEquals('Test Leave', $data['request_lists'][0]['leave']['title']);
        $this->assertEquals('Test Leave', $data['request_lists'][0]['leave']['type']);
        $this->assertEquals(null, $data['request_lists'][0]['leave']['total_days']);
        $this->assertEquals(0, $data['request_lists'][0]['leave']['is_half_day']);
        $this->assertEquals(null, $data['request_lists'][0]['leave']['half_day_configuration']);
        $this->assertEquals(Carbon::now()->format('M d, Y').' - '. Carbon::now()->addDay()->format('M d, Y'), $data['request_lists'][0]['leave']['leave_date']);
        $this->assertEquals('pending', $data['request_lists'][0]['leave']['status']);
        $this->assertEquals('leave', $data['type_lists'][0]);
    }

    public function testApprovalListDataApiFormat()
    {
        $response = $this->get("/v1/employee/approval-requests?type=&limit=1&offset=0", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('id',$data['request_lists'][0]);
        $this->assertArrayHasKey('type',$data['request_lists'][0]);
        $this->assertArrayHasKey('status',$data['request_lists'][0]);
        $this->assertArrayHasKey('id',$data['request_lists'][0]['leave']);
        $this->assertArrayHasKey('business_member_id',$data['request_lists'][0]['leave']);
        $this->assertArrayHasKey('title',$data['request_lists'][0]['leave']);
        $this->assertArrayHasKey('type',$data['request_lists'][0]['leave']);
        $this->assertArrayHasKey('total_days',$data['request_lists'][0]['leave']);
        $this->assertArrayHasKey('is_half_day',$data['request_lists'][0]['leave']);
        $this->assertArrayHasKey('half_day_configuration',$data['request_lists'][0]['leave']);
        $this->assertArrayHasKey('leave_date',$data['request_lists'][0]['leave']);
        $this->assertArrayHasKey('status',$data['request_lists'][0]['leave']);
        $this->assertArrayHasKey(0,$data['type_lists']);
    }
}
