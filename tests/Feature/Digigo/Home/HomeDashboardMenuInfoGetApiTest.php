<?php

namespace Tests\Feature\Digigo\Home;

use Database\Factories\ApprovalFlowFactory;
use Database\Factories\ApprovalRequestFactory;
use Database\Factories\BusinessMemberFactory;
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
        $this->assertEquals('Successful', $data['message']);
        $this->getUserDashboardMenuInfo($data);
        $this->returnUserDashboardMenuInfoDataInArrayFormat($data);
    }

    private function getUserDashboardMenuInfo($data)
    {
        /**
         *  is_approval_request_required and is_manager Data @return BusinessMemberFactory
         */
        $this->assertEquals(1, $data['info']['is_approval_request_required']);
        $this->assertEquals(0, $data['info']['is_manager']);

        /**
         *  pending_request count @return ApprovalRequestFactory
         */
        $this->assertEquals(1, $data['info']['pending_request']);
        $this->assertEquals(0, $data['info']['pending_visit_count']);
    }

    private function returnUserDashboardMenuInfoDataInArrayFormat($data)
    {
        $this->assertArrayHasKey('is_approval_request_required', $data['info']);
        $this->assertArrayHasKey('pending_request', $data['info']);
        $this->assertArrayHasKey('pending_visit_count', $data['info']);
        $this->assertArrayHasKey('is_manager', $data['info']);
    }
}
