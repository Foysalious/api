<?php

namespace Tests\Feature\Digigo\User;

use Carbon\Carbon;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class OfficeInfoGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    public function testApiReturnUserOfficialInformation()
    {
        $response = $this->get("/v1/employee/profile/1/official", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnValidDataForSuccessResponse()
    {
        $response = $this->get("/v1/employee/profile/1/official", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals('tisha@sheba.xyz', $data['official_info']['email']);
        $this->assertEquals('https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg', $data['official_info']['profile_picture']);
        $this->assertEquals(null, $data['official_info']['gender']);
        $this->assertEquals(1, $data['official_info']['department_id']);
        $this->assertEquals('IT', $data['official_info']['department']);
        $this->assertEquals('Manager', $data['official_info']['designation']);
        $this->assertEquals(Carbon::now()->format('d-m-Y'), $data['official_info']['joining_date']);
        $this->assertEquals(null, $data['official_info']['employee_id']);
        $this->assertEquals(null, $data['official_info']['employee_type']);
        $this->assertEquals(null, $data['official_info']['manager']);
        $this->assertEquals(1, $data['official_info']['is_updatable']);
    }

    public function testOfficialInfoDataApiFormat()
    {
        $response = $this->get("/v1/employee/profile/1/official", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('email', $data['official_info']);
        $this->assertArrayHasKey('profile_picture', $data['official_info']);
        $this->assertArrayHasKey('gender', $data['official_info']);
        $this->assertArrayHasKey('department_id', $data['official_info']);
        $this->assertArrayHasKey('department', $data['official_info']);
        $this->assertArrayHasKey('designation', $data['official_info']);
        $this->assertArrayHasKey('joining_date', $data['official_info']);
        $this->assertArrayHasKey('employee_id', $data['official_info']);
        $this->assertArrayHasKey('employee_type', $data['official_info']);
        $this->assertArrayHasKey('grade', $data['official_info']);
        $this->assertArrayHasKey('manager', $data['official_info']);
        $this->assertArrayHasKey('is_updatable', $data['official_info']);
    }
}
