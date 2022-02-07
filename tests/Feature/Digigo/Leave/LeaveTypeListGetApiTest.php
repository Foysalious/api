<?php

namespace Tests\Feature\Digigo\Leave;

use Sheba\Dal\LeaveType\Model as LeaveType;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class LeaveTypeListGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([LeaveType::class]);
        $this->logIn();
        LeaveType::factory()->create([
            'business_id' => $this->business->id
        ]);
    }

    public function testApiReturnLeaveTypeList()
    {
        $response = $this->get("/v1/employee/leaves/types", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnValidLeaveTypeList()
    {
        $response = $this->get("/v1/employee/leaves/types", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        foreach ($data['leave_types'] as $item) {
            $this->assertEquals(1, $item['id']);
            $this->assertEquals('Test Leave', $item['title']);
            $this->assertEquals(20, $item['total_days']);
            $this->assertEquals(0, $item['is_half_day_enable']);
            $this->assertEquals(20, $item['available_days']);
        }
        $this->assertEquals(null, $data['half_day_configuration']);
        $this->assertEquals('2021-07-01', $data['fiscal_year']['start_date']);
        $this->assertEquals('2022-06-30', $data['fiscal_year']['end_date']);
    }

    public function testEmployeeLevaveTypeListApiFormat()
    {
        $response = $this->get("/v1/employee/leaves/types", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        foreach ($data['leave_types'] as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('title', $item);
            $this->assertArrayHasKey('total_days', $item);
            $this->assertArrayHasKey('is_half_day_enable', $item);
            $this->assertArrayHasKey('available_days', $item);
        }
        $this->assertArrayHasKey('half_day_configuration', $data);
        $this->assertArrayHasKey('start_date', $data['fiscal_year']);
        $this->assertArrayHasKey('end_date', $data['fiscal_year']);
    }
}
