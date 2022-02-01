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
        $response->json();
        $this->assertEquals(200, $data['code']);
    }
}
