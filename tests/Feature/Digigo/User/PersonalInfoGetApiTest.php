<?php

namespace Tests\Feature\Digigo\User;

use App\Models\BusinessMember;
use App\Models\Profile;
use Carbon\Carbon;
use Database\Factories\BusinessMemberFactory;
use Database\Factories\ProfileFactory;
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
        Profile::first()->update([
            "gender" => 'Female',
            'dob' => Carbon::now(),
            'nationality' => 'Bangladeshi',
            'nid_no' => '2589631478',
            'passport_no' => '123456789',
            'blood_group' => 'O+',find(1)
        ]);

        BusinessMember::first()->update([
            "mobile" => '+8801620011019',
        ]);
        $response = $this->get("/v1/employee/profile/1/personal", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getUserPersonalDataFromDatabase($data);
        $this->returnUserPersonalDataInArrayFormat($data);
    }

    private function getUserPersonalDataFromDatabase($data)
    {
        /**
         *  personal data @return ProfileFactory
         */
        $this->assertEquals('Female', $data['emergency_contact_info']['gender']);
        $this->assertEquals(1, $data['emergency_contact_info']['id']);
        $this->assertEquals(Carbon::now()->isoformat('DD MMMM, YYYY'), $data['emergency_contact_info']['dob']);
        $this->assertEquals('Bangladeshi', $data['emergency_contact_info']['nationality']);
        $this->assertEquals('2589631478', $data['emergency_contact_info']['nid_no']);
        $this->assertEquals(null, $data['emergency_contact_info']['nid_front_image']);
        $this->assertEquals(null, $data['emergency_contact_info']['nid_back_image']);
        $this->assertEquals('123456789', $data['emergency_contact_info']['passport_no']);
        $this->assertEquals(null, $data['emergency_contact_info']['passport_image']);
        $this->assertEquals(null, $data['emergency_contact_info']['social_links']);
        /**
         *  user mobile number data @return BusinessMemberFactory
         */
        $this->assertEquals('+8801620011019', $data['emergency_contact_info']['mobile']);
    }


    private function returnUserPersonalDataInArrayFormat($data)
    {
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
