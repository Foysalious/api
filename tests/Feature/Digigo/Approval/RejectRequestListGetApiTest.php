<?php

namespace Tests\Feature\Digigo\Approval;

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
}
