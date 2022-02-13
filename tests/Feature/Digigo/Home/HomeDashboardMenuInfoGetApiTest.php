<?php

namespace Tests\Feature\Digigo\Home;

use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class HomeDashboardMenuInfoGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    public function testApiReturnApprovalRequestDataIfUserRoleIsManager()
    {
        $response = $this->get("/v1/employee/menu-info", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnValidDataForSuccessResponse()
    {
        $response = $this->get("/v1/employee/menu-info", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(1, $data['info']['is_approval_request_required']);
        $this->assertEquals(1, $data['info']['pending_request']);
        $this->assertEquals(0, $data['info']['pending_visit_count']);
        $this->assertEquals(0, $data['info']['is_manager']);
    }

    public function testHomeDashBoardMenuInfoApiFormat()
    {
        $response = $this->get("/v1/employee/menu-info", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('is_approval_request_required', $data['info']);
        $this->assertArrayHasKey('pending_request', $data['info']);
        $this->assertArrayHasKey('pending_visit_count', $data['info']);
        $this->assertArrayHasKey('is_manager', $data['info']);
    }
}
