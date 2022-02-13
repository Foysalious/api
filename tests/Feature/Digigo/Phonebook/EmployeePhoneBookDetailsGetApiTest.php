<?php

namespace Tests\Feature\Digigo\Phonebook;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Department;
use Carbon\Carbon;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class EmployeePhoneBookDetailsGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([BusinessDepartment::class]);
        $this->logIn();
        BusinessDepartment::factory()->create([
            'business_id' => 1,
        ]);
    }

    public function testApiReturnEmployeeDetailsIfEmployeeIdIsValid()
    {
        $response = $this->get("v1/employee/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnValidDataForSuccessResponse()
    {
        $response = $this->get("v1/employee/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(null, $data['details']['mobile']);
        $this->assertEquals('tisha@sheba.xyz', $data['details']['email']);
        $this->assertEquals('https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg', $data['details']['image']);
        $this->assertEquals('Manager', $data['details']['designation']);
        $this->assertEquals('IT', $data['details']['department']);
        $this->assertEquals('B+', $data['details']['blood_group']);
        $this->assertEquals(null, $data['details']['dob']);
        $this->assertEquals(null, $data['details']['social_link']);
    }

    public function testEmployeeDetailsDataApiFormat()
    {
        $response = $this->get("v1/employee/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('mobile', $data['details']);
        $this->assertArrayHasKey('email', $data['details']);
        $this->assertArrayHasKey('image', $data['details']);
        $this->assertArrayHasKey('designation', $data['details']);
        $this->assertArrayHasKey('department', $data['details']);
        $this->assertArrayHasKey('blood_group', $data['details']);
        $this->assertArrayHasKey('dob', $data['details']);
        $this->assertArrayHasKey('social_link', $data['details']);
    }
}
