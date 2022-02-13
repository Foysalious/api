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

    public function testApiReturnDashboardMenuList()
    {
        $response = $this->get("/v1/employee/dashboard-menu", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnValidDataForSuccessResponse()
    {
        $response = $this->get("/v1/employee/dashboard-menu", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals('Support', $data['dashboard'][0]['title']);
        $this->assertEquals('support', $data['dashboard'][0]['target_type']);
        $this->assertEquals('Attendance', $data['dashboard'][1]['title']);
        $this->assertEquals('attendance', $data['dashboard'][1]['target_type']);
        $this->assertEquals('Notice', $data['dashboard'][2]['title']);
        $this->assertEquals('notice', $data['dashboard'][2]['target_type']);
        $this->assertEquals('Expense', $data['dashboard'][3]['title']);
        $this->assertEquals('expense', $data['dashboard'][3]['target_type']);
        $this->assertEquals('Leave', $data['dashboard'][4]['title']);
        $this->assertEquals('leave', $data['dashboard'][4]['target_type']);
        $this->assertEquals('Approval', $data['dashboard'][5]['title']);
        $this->assertEquals('approval', $data['dashboard'][5]['target_type']);
        $this->assertEquals('Phonebook', $data['dashboard'][6]['title']);
        $this->assertEquals('phonebook', $data['dashboard'][6]['target_type']);
        $this->assertEquals('Payslip', $data['dashboard'][7]['title']);
        $this->assertEquals('payslip', $data['dashboard'][7]['target_type']);
        $this->assertEquals('Feedback', $data['dashboard'][8]['title']);
        $this->assertEquals('feedback', $data['dashboard'][8]['target_type']);
        $this->assertEquals('https://sheba.freshdesk.com/support/tickets/new', $data['dashboard'][8]['link']);
    }

    public function testDashboardMenuListApiFormat()
    {
        $response = $this->get("/v1/employee/dashboard-menu", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('title', $data['dashboard'][0]);
        $this->assertArrayHasKey('target_type', $data['dashboard'][0]);
        $this->assertArrayHasKey('title', $data['dashboard'][1]);
        $this->assertArrayHasKey('target_type', $data['dashboard'][1]);
        $this->assertArrayHasKey('title', $data['dashboard'][2]);
        $this->assertArrayHasKey('target_type', $data['dashboard'][2]);
        $this->assertArrayHasKey('title', $data['dashboard'][3]);
        $this->assertArrayHasKey('target_type', $data['dashboard'][3]);
        $this->assertArrayHasKey('title', $data['dashboard'][4]);
        $this->assertArrayHasKey('target_type', $data['dashboard'][4]);
        $this->assertArrayHasKey('title', $data['dashboard'][5]);
        $this->assertArrayHasKey('target_type', $data['dashboard'][5]);
        $this->assertArrayHasKey('title', $data['dashboard'][6]);
        $this->assertArrayHasKey('target_type', $data['dashboard'][6]);
        $this->assertArrayHasKey('title', $data['dashboard'][7]);
        $this->assertArrayHasKey('target_type', $data['dashboard'][7]);
        $this->assertArrayHasKey('title', $data['dashboard'][8]);
        $this->assertArrayHasKey('target_type', $data['dashboard'][8]);
        $this->assertArrayHasKey('link', $data['dashboard'][8]);

    }
}
