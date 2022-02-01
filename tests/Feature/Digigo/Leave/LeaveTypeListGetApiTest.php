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
}
