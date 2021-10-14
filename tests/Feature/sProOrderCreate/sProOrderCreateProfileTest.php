<?php namespace Tests\Feature\sProOrderCreate;

use Tests\Feature\FeatureTestCase;

class sProOrderCreateProfileTest extends FeatureTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->logIn();

        $this->profile -> update([
            'name' => 'Kazi Fahd Zakwan',
            'mobile' =>'+8801835559988',
        ]);

    }

    public function testSProProfileAPIWithValidPhoneNumber()
    {
        //arrange

        //act
        $response = $this->get('/v1/profiles?mobile=+8801835559988', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals('Kazi Fahd Zakwan', $data["profile"]["name"]);
    }

    public function testSProProfileAPIWithInvalidAlphabeticCharacterPhoneNumber()
    {
        //arrange

        //act
        $response = $this->get('/v1/profiles?mobile=abcdefghijk', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The mobile is an invalid bangladeshi number .', $data["message"]);
    }

    public function testSProProfileAPIWithInvalidSpecialCharacterPhoneNumber()
    {
        //arrange

        //act
        $response = $this->get('/v1/profiles?mobile=!@#$%^&*()!', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The mobile is an invalid bangladeshi number .', $data["message"]);
    }

    public function testSProProfileAPIWithoutPhoneNumberCode()
    {
        //arrange

        //act
        $response = $this->get('/v1/profiles?mobile=01835559988', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals('Kazi Fahd Zakwan', $data["profile"]["name"]);
    }

    public function testSProProfileAPIWithTenDigitPhoneNumber()
    {
        //arrange

        //act
        $response = $this->get('/v1/profiles?mobile=0183555998', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The mobile is an invalid bangladeshi number .', $data["message"]);
    }

    public function testSProProfileAPIWithTwelveDigitPhoneNumber()
    {
        //arrange

        //act
        $response = $this->get('/v1/profiles?mobile=+880183555998', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The mobile is an invalid bangladeshi number .', $data["message"]);
    }

    public function testSProProfileAPIWithFourteenDigitPhoneNumber()
    {
        //arrange

        //act
        $response = $this->get('/v1/profiles?mobile=+88018355599888', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The mobile is an invalid bangladeshi number .', $data["message"]);
    }

    public function testSProProfileAPIWithTwelveDigitPhoneNumberWithoutNumberCode()
    {
        //arrange

        //act
        $response = $this->get('/v1/profiles?mobile=018355599888', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The mobile is an invalid bangladeshi number .', $data["message"]);
    }

    public function testSProProfileAPIWithoutPhoneNumberParameter()
    {
        //arrange

        //act
        $response = $this->get('/v1/profiles', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The mobile field is required.', $data["message"]);
    }

    public function testSProProfileAPIWithValidPhoneNumberThatDoesNotExistInDB()
    {
        //arrange

        //act
        $response = $this->get('/v1/profiles?mobile=+8801835559999', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Profile Not Found', $data["message"]);
    }

    public function testSProProfileAPIWithValidPhoneNumberWithoutNumberCodeThatDoesNotExistInDB()
    {
        //arrange

        //act
        $response = $this->get('/v1/profiles?mobile=01835559999', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Profile Not Found', $data["message"]);
    }

}
