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
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->truncateTables([Department::class, BusinessDepartment::class, BusinessRole::class, BusinessMember::class]);
        $this->logIn();
        Department::factory()->create();
        BusinessDepartment::factory()->create([
            'business_id'  => 1,
        ]);
        BusinessRole::factory()->create();
        BusinessMember::factory()->create([
            'business_id'       =>$this->business->id,
            'member_id'         => $this->member->id,
            'employee_id'       =>1,
            'manager_id'        =>1,
            'join_date'         => Carbon::now(),
            'mobile'            =>'+8801620011019',
            'business_role_id'  => 1,
        ]);
    }

    public function testCheckAPiReturnEmployeeDetailsIfEmployeeIdIsValid()
    {
        $response = $this->get("v1/employee/1", [
            'Authorization'     => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }

}