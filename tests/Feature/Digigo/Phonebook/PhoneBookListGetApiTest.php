<?php

namespace Tests\Feature\Digigo\Phonebook;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Member;
use Carbon\Carbon;
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
        BusinessDepartment::factory()->create([
            'business_id' => 1,
        ]);
    }

    public function testApiReturnAllEmployeeContactListUnderACompany()
    {
        $response = $this->get("v1/employee?for=phone_book", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }
}
