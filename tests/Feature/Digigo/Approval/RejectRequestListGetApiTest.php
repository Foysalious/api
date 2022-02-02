<?php

namespace Tests\Feature\Digigo\Approval;

use Carbon\Carbon;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class RejectRequestListGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    public function testApiReturnRejectReasonListForRejectAnyLeaveRequest()
    {
        $response = $this->get("/v1/employee/leaves/reject-reasons", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }
    public function testApiReturnEmployeeRejectRequestListData()
    {
        $response = $this->get("/v1/employee/leaves/reject-reasons", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals('violation_of_leave_policy', $data['reject_reasons'][0]['key']);
        $this->assertEquals('Violation of leave policy', $data['reject_reasons'][0]['value']);
        $this->assertEquals('not_a_valid_leave_request', $data['reject_reasons'][1]['key']);
        $this->assertEquals('Not a valid leave request', $data['reject_reasons'][1]['value']);
        $this->assertEquals('very_frequent_leave_requests', $data['reject_reasons'][2]['key']);
        $this->assertEquals('Very frequent leave requests', $data['reject_reasons'][2]['value']);
        $this->assertEquals('other', $data['reject_reasons'][3]['key']);
        $this->assertEquals('Other', $data['reject_reasons'][3]['value']);
    }

    public function testEmployeeRejectRequestListDataApiFormat()
    {
        $response = $this->get("/v1/employee/leaves/reject-reasons", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('key', $data['reject_reasons'][0]);
        $this->assertArrayHasKey('value', $data['reject_reasons'][0]);
        $this->assertArrayHasKey('key', $data['reject_reasons'][1]);
        $this->assertArrayHasKey('key', $data['reject_reasons'][1]);
        $this->assertArrayHasKey('key', $data['reject_reasons'][2]);
        $this->assertArrayHasKey('key', $data['reject_reasons'][2]);
        $this->assertArrayHasKey('key', $data['reject_reasons'][3]);
        $this->assertArrayHasKey('key', $data['reject_reasons'][3]);
    }
}
