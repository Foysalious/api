<?php namespace Tests\Feature\UserProfileUpdate;

use App\Models\Affiliate;
use App\Models\Customer;
use App\Models\Profile;
use Tests\Feature\FeatureTestCase;

class UserProfileUpdateTest extends FeatureTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->truncateTable(Profile::class,Customer::class);

        $this->logIn();

    }

    public function testUserProfileUpdateAPIForResponse200()
    {

        //arrange

        //act

        //dd("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->token);

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?",[
            'remember_token' =>  "Token x",
            'is_old_user' => 1
        ]);
        $data = $response->decodeResponseJson();
        dd($data);

        //assert
//        $this->assertEquals(200, $data["code"]);
//        $this->assertEquals("Successful", $data["message"]);
//        $this->assertEquals('Ac service', $data["info_call_lists"][0]["service_name"]);
//        $this->assertEquals('Open', $data["info_call_lists"][0]["status"]);

    }
}
