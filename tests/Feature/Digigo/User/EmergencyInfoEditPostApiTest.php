<?php

namespace Tests\Feature\Digigo\User;

use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class EmergencyInfoEditPostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    public function testApiShouldReturnOKResponseIfUserUpdateAnyEmergencyData()
    {
        $response = $this->post("/v2/employee/profile/emergency", [
            'name' => 'Sadab',
            'mobile' => '+8801819069484',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }
}
