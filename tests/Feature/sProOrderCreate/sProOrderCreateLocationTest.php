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
            'geo_informations' => '{"lat":"23.814800953807","lng":"90.362328935888","radius":"1000"}'
        ]);

    }

    public function testSProLocationAPIWithPartnerId()
    {
        //arrange

        //act
        $response = $this->get('/v2/partners/' . $this->partner->id . '/locations');

        $data = $response->decodeResponseJson();
        dd($data);

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
    }
}
