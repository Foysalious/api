<?php

namespace Tests\Feature\Digigo\Home;

use Sheba\Dal\PayrollSetting\PayrollSetting;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class HomeDashboardMenuGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([PayrollSetting::class]);
        $this->logIn();
        PayrollSetting::factory()->create([
            'business_id' => $this->business->id
        ]);
    }

    public function testCheckAPiReturnDashboardMenuList()
    {
        $response = $this->get("/v1/employee/dashboard-menu", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }
}