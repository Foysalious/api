<?php

namespace Tests\Feature\Digigo\Phonebook;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Profile;
use Carbon\Carbon;
use Database\Factories\BusinessMemberFactory;
use Database\Factories\BusinessRoleFactory;
use Database\Factories\MemberFactory;
use Database\Factories\ProfileFactory;
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

    public function testApiReturnEmployeeDetailsAccordingToBusinessId()
    {
        Profile::first()->update([
            'dob' => Carbon::now(),
            'blood_group' => 'O+',
        ]);

        BusinessMember::first()->update([
            "mobile" => '+8801620011019',
        ]);
        $response = $this->get("v1/employee/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getEmployeesContactDetailsFromDatabase($data);
        $this->returnEmployeesContactDetailsDataInArrayFormat($data);
    }

    private function getEmployeesContactDetailsFromDatabase($data)
    {
        /**
         *  User Mobile Data @return BusinessMemberFactory
         */
        $this->assertEquals('+8801620011019', $data['details']['mobile']);
        /**
         *  User email, blood group, dob, social link and image Data @return ProfileFactory
         */
        $this->assertEquals('tisha@sheba.xyz', $data['details']['email']);
        $this->assertEquals('https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg', $data['details']['image']);
        $this->assertEquals('O+', $data['details']['blood_group']);
        $this->assertEquals(Carbon::now()->format('Y-m-d'), Carbon::parse($data['details']['dob'])->format('Y-m-d'));
        $this->assertEquals(null, $data['details']['social_link']);
        /**
         *  designation data @return BusinessMemberFactory
         */
        $this->assertEquals('IT', $data['details']['department']);
        /**
         *  designation data @return BusinessMemberFactory as business_role_id
         * business_role_id  @return BusinessRoleFactory
         */
        $this->assertEquals('Manager', $data['details']['designation']);
    }

    private function returnEmployeesContactDetailsDataInArrayFormat($data)
    {
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
