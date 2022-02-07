<?php

namespace Tests\Feature\Digigo\User;

use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class PersonalInfoGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    public function testApiReturnUserPersonalInformation()
    {
        $response = $this->get("/v1/employee/profile/1/personal", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnValidDataForSuccessResponse()
    {
        $response = $this->get("/v1/employee/profile/1/personal", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(null, $data['emergency_contact_info']['gender']);
        $this->assertEquals(1, $data['emergency_contact_info']['id']);
        $this->assertEquals(null, $data['emergency_contact_info']['mobile']);
        $this->assertEquals(null, $data['emergency_contact_info']['dob']);
        $this->assertEquals(null, $data['emergency_contact_info']['nationality']);
        $this->assertEquals(null, $data['emergency_contact_info']['nid_no']);
        $this->assertEquals(null, $data['emergency_contact_info']['nid_front_image']);
        $this->assertEquals(null, $data['emergency_contact_info']['nid_back_image']);
        $this->assertEquals(null, $data['emergency_contact_info']['passport_no']);
        $this->assertEquals(null, $data['emergency_contact_info']['passport_image']);
        $this->assertEquals(null, $data['emergency_contact_info']['social_links']);
    }

    public function testPersonalInfoDataApiFormat()
    {
        $response = $this->get("/v1/employee/profile/1/personal", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('gender', $data['emergency_contact_info']);
        $this->assertArrayHasKey('id', $data['emergency_contact_info']);
        $this->assertArrayHasKey('mobile', $data['emergency_contact_info']);
        $this->assertArrayHasKey('dob', $data['emergency_contact_info']);
        $this->assertArrayHasKey('nationality', $data['emergency_contact_info']);
        $this->assertArrayHasKey('nid_no', $data['emergency_contact_info']);
        $this->assertArrayHasKey('nid_front_image', $data['emergency_contact_info']);
        $this->assertArrayHasKey('nid_back_image', $data['emergency_contact_info']);
        $this->assertArrayHasKey('passport_no', $data['emergency_contact_info']);
        $this->assertArrayHasKey('passport_image', $data['emergency_contact_info']);
        $this->assertArrayHasKey('blood_group', $data['emergency_contact_info']);
        $this->assertArrayHasKey('social_links', $data['emergency_contact_info']);
    }
}
