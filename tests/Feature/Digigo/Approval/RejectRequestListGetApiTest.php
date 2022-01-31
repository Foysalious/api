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
    public function testCheckAPiReturnRejectReasonListForRejectAnyLeaveRequest()
    {
        $response = $this->get("/v1/employee/leaves/reject-reasons", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }

}