<?php

namespace Tests\Feature\Digigo;

use App\Models\Profile;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */

class DigigoLoginPostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function testCheckApiShouldReturnOKResponseIfEmailAndPasswordIsValid()
    {
        $profile = Profile::find(1);
        $profile->update(["password" => bcrypt('123456')]);
        $response = $this->post('/v1/employee/login', [
            'email' => 'tisha@sheba.xyz','password' => '123456'
        ]);
        $data = $response->decodeResponseJson();
        $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }

}