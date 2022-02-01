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

    public function testCheckAPiReturnApprovalRequestDataIfUserRoleIsManager()
    {
        $response = $this->get("/v1/employee/menu-info", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }
}
