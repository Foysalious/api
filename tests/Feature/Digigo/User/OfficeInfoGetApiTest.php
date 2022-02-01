<?php

namespace Tests\Feature\Digigo\User;

use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class OfficeInfoGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    public function testApiReturnUserOfficialInformation()
    {
        $response = $this->get("/v1/employee/profile/1/official", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }
}
