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
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }

    public function testEmployeePersonalInfoWillUpdate()
    {
        $response = $this->post("/v2/employee/profile/personal", [
            'address' => 'North/Badda-1212',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $response->json();
        $profile = Profile::first();
        $this->assertEquals('North/Badda-1212', $profile->address);
    }
}
