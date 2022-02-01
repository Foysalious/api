<?php

namespace Tests\Feature\Digigo\User;

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

    public function testCheckAPiReturnUserPersonalInformation()
    {
        $response = $this->get("/v1/employee/profile/1/personal", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }
}
