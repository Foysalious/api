<?php

namespace Tests\Feature\Digigo\User;

use App\Models\Member;
use App\Models\Profile;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class PersonalInfoEditPostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    public function testApiShouldReturnOKResponseIfUserUpdateAnyPersonalData()
    {
        $response = $this->post("/v2/employee/profile/personal", [
            'address' => 'North/Badda-1212',
            'mobile' => '+8801678242955',
            'dob' => '1995-06-12',
            'nationality' => 'Bangladeshi',
            'nid_no' => '2589631478',
            'passport_no' => '123456789',
            'blood_group' => 'O+',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $profile = Profile::first();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->assertEquals('North/Badda-1212', $profile->address);
        $this->assertEquals('+8801678242955', $profile->mobile);
        $this->assertEquals('1995-06-12', $profile->dob);
        $this->assertEquals('Bangladeshi', $profile->nationality);
        $this->assertEquals('2589631478', $profile->nid_no);
        $this->assertEquals('123456789', $profile->passport_no);
        $this->assertEquals('O+', $profile->blood_group);
    }
}
