<?php

namespace Tests\Feature\Digigo\Leave;

use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class LeaveSettingsGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Leave::class, LeaveType::class]);
        $this->logIn();
        LeaveType::factory()->create([
            'business_id' => $this->business->id
        ]);
        Leave::factory()->create([
            'business_member_id' => $this->business_member->id
        ]);
    }

    public function testCheckAPiReturnLeaveSettingsInfo()
    {
        $response = $this->get("/v1/employee/leaves/settings", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }

}
