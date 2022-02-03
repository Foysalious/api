<?php

namespace Tests\Feature\Digigo\User;

use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class EmergencyInfoGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    public function testApiReturnUserEmergencyInformation()
    {
        $response = $this->get("/v1/employee/profile/1/emergency", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnValidDataForSuccessResponse()
    {
        $response = $this->get("/v1/employee/profile/1/emergency", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals('https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg', $data['emergency_contact_info']['profile_picture']);
        $this->assertEquals(null, $data['emergency_contact_info']['emergency_name']);
        $this->assertEquals(null, $data['emergency_contact_info']['emergency_number']);
        $this->assertEquals(null, $data['emergency_contact_info']['emergency_person_relationship']);
    }
}
