<?php namespace Tests\Feature\UserProfileUpdate;

use App\Models\Customer;
use App\Models\Profile;
use Carbon\Carbon;
use Tests\Feature\FeatureTestCase;

class UserProfileCreateTest extends FeatureTestCase
{
    private $today;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTable(Profile::class,Customer::class);

        $this->logIn();

    }

    public function testUserProfileCreateAPIByUpdatingName()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'is_old_user' => 0,
            'name' => 'John Doe'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileCreateAPIByUpdatingEmail()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'email'=> 'johndoe@gmail.com',
            'is_old_user' => 0
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileCreateAPIByUpdatingDateOfBirth()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => '2009-6-29',
            'is_old_user' => 0
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileCreateAPIByUpdatingGender()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'gender' => 'Male',
            'is_old_user' => 0
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileCreateAPIByUpdatingAddress()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'address' => 'Azimpur Khoborsthan',
            'is_old_user' => 0
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileCreateAPIByUpdatingDateOfBirthGender()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => '2009-6-29',
            'gender' => 'Male',
            'is_old_user' => 0
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileCreateAPIByUpdatingDateOfBirthName()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => '2009-6-29',
            'is_old_user' => 0,
            'name' => 'John Doe'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileCreateAPIByUpdatingDateOfBirthGenderName()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => '2009-6-29',
            'gender' => 'Male',
            'is_old_user' => 0,
            'name' => 'John Doe'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileCreateAPIByUpdatingDateOfBirthEmailGenderName()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => '2009-6-29',
            'email'=> 'johndoe@gmail.com',
            'gender' => 'Male',
            'is_old_user' => 0,
            'name' => 'John Doe'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileCreateAPIByUpdatingDateOfBirthEmailGenderNameAddress()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => '2009-6-29',
            'email'=> 'johndoe@gmail.com',
            'gender' => 'Male',
            'is_old_user' => 0,
            'name' => 'John Doe',
            'address' => 'Dhaka, Bangladesh'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);

    }

    public function testUserProfileCreateAPIByGivingInvalidGender()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => '2009-6-29',
            'is_old_user' => 0,
            'email'=> 'johndoe@gmail.com',
            'gender' => 'dkjsfshkdf',
            'name' => 'John Doe',
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The selected gender is invalid.", $data["message"]);

    }

    public function testUserProfileCreateAPIByGivingInvalidRememberToken()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=dhksueislbdnf",[
            'dob' => '2009-6-29',
            'is_old_user' => 0,
            'email'=> 'johndoe@gmail.com',
            'gender' => 'Male',
            'name' => 'John Doe'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(404, $data["code"]);
        $this->assertEquals("User not found.", $data["message"]);

    }

    public function testUserProfileCreateAPIByNotGivingRememberToken()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?",[
            'dob' => '2009-6-29',
            'is_old_user' => 0,
            'email'=> 'johndoe@gmail.com',
            'gender' => 'Male',
            'name' => 'John Doe'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("Authentication token is missing from the request.", $data["message"]);

    }

    public function testUserProfileCreateAPIByGivingInvalidCustomerId()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/123456/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => '2009-6-29',
            'email'=> 'johndoe@gmail.com',
            'gender' => 'Male',
            'is_old_user' => 0,
            'name' => 'John Doe'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(403, $data["code"]);
        $this->assertEquals("You're not authorized to access this user.", $data["message"]);

    }

    public function testUserProfileCreateAPIByGivingInvalidDateOfBirth()
    {

        //arrange

        $today = Carbon::now()->toDateString();

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => '2028-06-25',
            'email'=> 'johndoe@gmail.com',
            'gender' => 'Male',
            'is_old_user' => 0,
            'name' => 'John Doe'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The dob must be a date before " . $today . ".", $data["message"]);

    }

    public function testUserProfileCreateAPIByGivingCharacterInputInDateOfBirth()
    {

        //arrange

        $today = Carbon::now()->toDateString();

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => 'sbhfjkfs',
            'email'=> 'johndoe@gmail.com',
            'gender' => 'Male',
            'is_old_user' => 0,
            'name' => 'John Doe'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The dob is not a valid date.The dob does not match the format Y-m-d.The dob must be a date before " . $today . ".", $data["message"]);

    }

    public function testUserProfileCreateAPIByGivingInvalidEmail()
    {

        //arrange

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => '2009-6-29',
            'email'=> 'johndoe@gmail',
            'gender' => 'Male',
            'is_old_user' => 0,
            'name' => 'John Doe'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The email must be a valid email address.", $data["message"]);

    }

    public function testUserProfileCreateAPIByGivingInvalidDateOfBirthGenderEmail()
    {

        //arrange

        $today = Carbon::now()->toDateString();

        //act

        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
            'dob' => 'hfdkhf',
            'email'=> 'johndoe@gmail',
            'gender' => 'Others',
            'is_old_user' => 0,
            'name' => 'John Doe'
        ]);

        $data = $response->decodeResponseJson();

        //assert

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The selected gender is invalid.The dob is not a valid date.The dob does not match the format Y-m-d.The dob must be a date before " . $today . ".The email must be a valid email address.", $data["message"]);

    }

    //    public function testUserProfileUpdateAPIV3ByGivingInvalidName()
//    {
//
//        //arrange
//
//        //act
//
//        $response = $this->put("/v3/customers/" . $this->customer->id . "/edit?remember_token=" . $this->customer->remember_token,[
//            'dob' => '2009-6-29',
//            'email'=> 'johndoe@gmail.com',
//            'gender' => 'Male',
//            'is_old_user' => 0,
//            'name' => '123456'
//        ]);
//
//        $data = $response->decodeResponseJson();
//
//        //assert
//
//        $this->assertEquals(400, $data["code"]);
//        $this->assertEquals("The name must be a valid name.", $data["message"]);
//
//    }
}
