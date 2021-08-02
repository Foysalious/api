<?php namespace Tests\Feature\UserProfileUpdate;

use App\Models\Affiliate;
use App\Models\Customer;
use App\Models\Profile;
use Carbon\Carbon;
use Tests\Feature\FeatureTestCase;

class UserProfileUpdateV1Test extends FeatureTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->truncateTable(Profile::class,Customer::class);

        $this->logIn();

    }

    public function testUserProfileUpdateAPIV1ByUpdatingNameGenderBirthdayAddress()
    {

        //arrange

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'name',
            'value' => 'John Doe',

            'field' => 'gender',
            'value' => 'Male',

            'field' => 'birthday',
            'value' => '2007-06-27',

            'field' => 'address',
            'value' => 'Dhaka, Bangladesh'
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileUpdateAPIV1ByUpdatingNameOnly()
    {

        //arrange

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'name',
            'value' => 'John Doe',
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileUpdateAPIV1ByUpdatingGenderOnly()
    {

        //arrange

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'gender',
            'value' => 'Female',
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileUpdateAPIV1ByUpdatingBirthdayOnly()
    {

        //arrange

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'birthday',
            'value' => '2005-06-27'
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileUpdateAPIV1ByUpdatingAddressOnly()
    {

        //arrange

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'address',
            'value' => 'Dhaka, Bangladesh'
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileUpdateAPIV1ByGivingNullValueOfName()
    {

        //arrange

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'name',
            'value' => '',
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The value field is required.", $data["message"]);

    }

    public function testUserProfileUpdateAPIV1ByGivingNullValueOfGender()
    {

        //arrange

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'gender',
            'value' => ''
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The value field is required.", $data["message"]);

    }

    public function testUserProfileUpdateAPIV1ByGivingNullValueOfBirthday()
    {

        //arrange

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'birthday',
            'value' => ''
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The value field is required.", $data["message"]);

    }

    public function testUserProfileUpdateAPIV1ByGivingNullValueOfAddress()
    {

        //arrange

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'address',
            'value' => ''
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The value field is required.", $data["message"]);

    }

    public function testUserProfileUpdateAPIV1ByGivingInvalidBirthday()
    {

        //arrange

        $today = Carbon::now()->toDateString();

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'birthday',
            'value' => '2222-06-29'
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The value must be a date before " . $today . ".", $data["message"]);

    }

    public function testUserProfileUpdateAPIV1ByGivingCharacterInputInBirthday()
    {

        //arrange

        $today = Carbon::now()->toDateString();

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'birthday',
            'value' => 'jgfffs'
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The value is not a valid date.The value does not match the format Y-m-d.The value must be a date before " . $today . ".", $data["message"]);

    }

    public function testUserProfileUpdateAPIV1ByGivingInvalidGender()
    {

        //arrange

        //act

        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'field' => 'gender',
            'value' => 'hjsfgaf'
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The selected value is invalid.", $data["message"]);

    }

//    public function testUserProfileUpdateAPIV1ByGivingInvalidName()
//    {
//
//        //arrange
//
//        //act
//
//        $response = $this->put("/v1/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
//            'field' => 'name',
//            'value' => '123456'
//        ]);
//        $data = $response->decodeResponseJson();
//
//        $this->assertEquals(400, $data["code"]);
//        $this->assertEquals("The selected value is invalid.", $data["message"]);
//
//    }


}
