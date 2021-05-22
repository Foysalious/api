<?php

/**
 * Khairun Nahar
 * 22 May,2021
 */


namespace Tests\Feature\sDeliverOrderPlacement;


use Tests\Feature\FeatureTestCase;

class OrderPlaceUpzillasAPITest extends FeatureTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->logIn();


    }


    public function testGetDeliveryAddressUpzillaAccordingDhakaDistrict()
    {

        $response = $this->get('/v2/pos/delivery/upzillas/Dhaka/district');
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);

    }
}