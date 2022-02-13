<?php

namespace Tests\Feature\Digigo\Phonebook;

use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use Carbon\Carbon;
use Database\Factories\BusinessMemberFactory;
use Database\Factories\BusinessRoleFactory;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class PhoneBookListGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([BusinessDepartment::class]);
        $this->logIn();
    }

    public function testApiReturnAllEmployeeContactListForSpecificBusinessId()
    {
        $this->createNewDepartment();
        Profile::first()->update([
            'dob' => Carbon::now(),
            'blood_group' => 'O+',
        ]);
        Profile::find(2)->update([
            'dob' => Carbon::now(),
            'blood_group' => 'O+',
        ]);

        BusinessMember::first()->update([
            "mobile" => '+8801620011019',
        ]);

        BusinessMember::find(2)->update([
            "mobile" => '+8801620011017',
        ]);
        $response = $this->get("v1/employee?for=phone_book", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getEmployeeContactListFromDatabase($data);
        $this->returnEmployeeContactListDataInArrayFormat($data);
    }

    private function createNewDepartment()
    {
        $this->profile = Profile::factory()->create([
            'mobile' => '+8801620011017',
            'email' => 'mahanaz@sheba.xyz',
        ]);
        $this->member = Member::factory()->for($this->profile)->create();
        $this->business = Business::factory()->create();
        $this->businessDepartment = BusinessDepartment::factory()->create([
            'business_id' => 1,
            'name' => 'HR',
            'is_published' => 1,
        ]);
        BusinessRole::factory()->create([
            'business_department_id' => 2
        ]);
        $this->business_member = BusinessMember::factory()->create([
            'business_id' => 1,
            'member_id' => 2,
            'department' => 2,
            'business_role_id' => 2,
            'manager_id' => 1
        ]);

    }

    private function getEmployeeContactListFromDatabase($data)
    {
        foreach ($data['employees']['IT'] as $item) {
            /**
             *  id and mobile number @return BusinessMemberFactory
             */
            $this->assertEquals(1, $item['id']);
            $this->assertEquals('+8801620011019', $item['mobile']);
            $this->assertEquals('https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg', $item['pro_pic']);


            /**
             *  designation data @return BusinessMemberFactory as business_role_id
             * business_role_id  @return BusinessRoleFactory
             */
            $this->assertEquals('Manager', $item['designation']);
            /**
             *  is_employee_new_joiner data calculate from join_date @return BusinessMemberFactory.
             * Employee be new joiner after 30 days of joining dates according to isNewJoiner() function
             *
             */
            $this->assertEquals(false, $item['is_employee_new_joiner']);
        }

        foreach ($data['employees']['HR'] as $item) {
            /**
             *  id and mobile number @return BusinessMemberFactory
             */
            $this->assertEquals(2, $item['id']);
            $this->assertEquals('+8801620011017', $item['mobile']);
            $this->assertEquals('https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg', $item['pro_pic']);
            /**
             *  designation data @return BusinessMemberFactory as business_role_id
             * business_role_id  @return BusinessRoleFactory
             */
            $this->assertEquals('Manager', $item['designation']);
            /**
             *  is_employee_new_joiner data calculate from join_date @return BusinessMemberFactory.
             * Employee be new joiner after 30 days of joining dates according to isNewJoiner() function
             *
             */
            $this->assertEquals(false, $item['is_employee_new_joiner']);
        }
        $this->assertEquals('IT', $data['departments'][0]);
        $this->assertEquals('HR', $data['departments'][1]);
    }

    private function returnEmployeeContactListDataInArrayFormat($data)
    {
        foreach ($data['employees']['HR'] as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('mobile', $item);
            $this->assertArrayHasKey('designation', $item);
            $this->assertArrayHasKey('is_employee_new_joiner', $item);
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('mobile', $item);
            $this->assertArrayHasKey('designation', $item);
            $this->assertArrayHasKey('is_employee_new_joiner', $item);
        }
        foreach ($data['employees']['HR'] as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('mobile', $item);
            $this->assertArrayHasKey('designation', $item);
            $this->assertArrayHasKey('is_employee_new_joiner', $item);
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('mobile', $item);
            $this->assertArrayHasKey('designation', $item);
            $this->assertArrayHasKey('is_employee_new_joiner', $item);
        }

    }
}
