<?php

namespace Tests\Feature\Digigo\User;

use App\Models\Member;
use Carbon\Carbon;
use Database\Factories\MemberFactory;
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

    public function testApiUpdateUserEmergencyDataAndUpdatedDataStoreInMemberTable()
    {
        $response = $this->post("/v2/employee/profile/emergency", [
            'name' => 'Sadab',
            'mobile' => '+8801819069484',
            'relationship' => 'brother',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $member = Member::first();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        /**
         *  Emergency info @return MemberFactory
         */
        $this->assertEquals('+8801819069484', $member->emergency_contract_person_number);
        $this->assertEquals('Sadab', $member->emergency_contract_person_name);
        $this->assertEquals('brother', $member->emergency_contract_person_relationship);
    }

}
