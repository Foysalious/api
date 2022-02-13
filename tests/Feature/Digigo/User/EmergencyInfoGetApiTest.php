<?php

namespace Tests\Feature\Digigo\User;

use App\Models\Member;
use Database\Factories\BusinessMemberFactory;
use Database\Factories\MemberFactory;
use Illuminate\Support\Facades\DB;
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

    public function testApiReturnUserEmergencyInformationFromTable()
    {
        Member::find(1)->update([
            "emergency_contract_person_name" => 'Sadab',
            "emergency_contract_person_number" => '+8801620011019',
            "emergency_contract_person_relationship" => 'Brother'
        ]);
        $response = $this->get("/v1/employee/profile/1/emergency", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getUserEmergencyDataFromDatabase($data);
        $this->returnUserEmergencyDataInArrayFormat($data);

    }

    private function getUserEmergencyDataFromDatabase($data)
    {
        /**
         *  User Emergency Data @return MemberFactory
         */
        $this->assertEquals('https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg', $data['emergency_contact_info']['profile_picture']);
        $this->assertEquals('Sadab', $data['emergency_contact_info']['emergency_name']);
        $this->assertEquals('+8801620011019', $data['emergency_contact_info']['emergency_number']);
        $this->assertEquals('Brother', $data['emergency_contact_info']['emergency_person_relationship']);
    }


    private function returnUserEmergencyDataInArrayFormat($data)
    {
        $this->assertArrayHasKey('profile_picture', $data['emergency_contact_info']);
        $this->assertArrayHasKey('emergency_name', $data['emergency_contact_info']);
        $this->assertArrayHasKey('emergency_number', $data['emergency_contact_info']);
        $this->assertArrayHasKey('emergency_person_relationship', $data['emergency_contact_info']);
    }
}
