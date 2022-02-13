<?php

namespace Tests\Feature\Digigo\Login;

use App\Models\Profile;
use Sheba\OAuth2\AccountServerClient;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockAccountServerClient;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class DigigoLoginPostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(AccountServerClient::class, MockAccountServerClient::class);
    }

    public function testApiShouldReturnOKResponseIfEmailAndPasswordIsValid()
    {
        $this->logIn();
        MockAccountServerClient::$token = $this->token;
        $response = $this->post('/v1/employee/login', [
            'email' => 'tisha@sheba.xyz', 'password' => '12345'
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }

    public function testLogInApiReturnUserProfileInfo()
    {
        $this->logIn();
        MockAccountServerClient::$token = $this->token;
        $response = $this->post('/v1/employee/login', [
            'email' => 'tisha@sheba.xyz', 'password' => '12345'
        ]);
        $data = $response->json();
        $this->assertNotNull($data['token'], "token is not null");
        $this->assertEquals("+8801678242955", $data['user']['mobile']);
        $this->assertEquals(1, $data['user']['business_id']);
        $this->assertEquals("My Company", $data['user']['business_name']);
        $this->assertEquals(true, $data['user']['is_remote_attendance_enable']);
    }
}
