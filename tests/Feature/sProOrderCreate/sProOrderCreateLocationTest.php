<?php namespace Tests\Feature\sProOrderCreate;

use Sheba\Dal\PartnerLocation\PartnerLocation;
use Tests\Feature\FeatureTestCase;

class sProOrderCreateLocationTest extends FeatureTestCase
{
    private $partnerLocation;

    public function setUp()
    {

        parent::setUp();

        $this->logIn();

        $this->partner ->update([
            'geo_informations' => '{"lat":"23.814800953807","lng":"90.362328935888","radius":"100"}'
        ]);

    }

    public function testSProLocationAPIWithValidPartnerId()
    {
        //arrange

        //act
        $response = $this->get('/v2/partners/' . $this->partner->id . '/locations');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
    }

    public function testSProLocationAPIWithInvalidPartnerId()
    {
        //arrange

        //act
        $response = $this->get('/v2/partners/11111/locations');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);
    }

    public function testSProLocationAPIWithInvalidAlphabeticCharacterPartnerId()
    {
        //arrange

        //act
        $response = $this->get('/v2/partners/abcde/locations');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);
    }

    public function testSProLocationAPIWithInvalidSpecialCharacterPartnerId()
    {
        //arrange

        //act
        $response = $this->get('/v2/partners/!@#$%^&*()/locations');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data["code"]);
        $this->assertEquals('Not found', $data["message"]);
    }

    public function testSProLocationAPIWithPutMethod()
    {
        //arrange

        //act
        $response = $this->put('/v2/partners/' . $this->partner->id . '/locations');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('405 Method Not Allowed', $data["message"]);
    }

    public function testSProLocationAPIWithPostMethod()
    {
        //arrange

        //act
        $response = $this->post('/v2/partners/' . $this->partner->id . '/locations');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('405 Method Not Allowed', $data["message"]);
    }

    public function testSProLocationAPIWithDeleteMethod()
    {
        //arrange

        //act
        $response = $this->delete('/v2/partners/' . $this->partner->id . '/locations');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('405 Method Not Allowed', $data["message"]);
    }

    public function testSProLocationAPIWithInvalidUrl()
    {
        //arrange

        //act
        $response = $this->get('/v2/partnerss/' . $this->partner->id . '/locations');

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals('404 Not Found', $data["message"]);
    }

}
