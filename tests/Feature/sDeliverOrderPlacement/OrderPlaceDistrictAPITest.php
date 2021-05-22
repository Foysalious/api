<?php

/**
 * Khairun Nahar
 * 22 May,2021
 */


namespace Tests\Feature\sDeliverOrderPlacement;


use Tests\Feature\FeatureTestCase;

class OrderPlaceDistrictAPITest extends FeatureTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->logIn();


    }


    public function testGetDeliveryAddressDistrict()
    {

        $response = $this->get('/v2/pos/delivery/district');
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);

    }

    public function testGetDeliveryAddressDistrictList()
    {

        $response = $this->get('/v2/pos/delivery/district');
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals('Bagerhat', $data['data'][0]['name']);
        $this->assertEquals('Bagerhat', $data['data'][0]['display_name']);



    }


}