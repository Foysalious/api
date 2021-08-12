<?php namespace Tests\Feature\sDeliverOrderPlacement;

/**
 * Khairun Nahar
 * 22 May,2021
 */

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

    public function testFetchListGetDeliveryAddressDistrict()
    {
        $response = $this->get('/v2/pos/delivery/district');
        $data = $response->decodeResponseJson();
        $this->assertEquals('Bagerhat', $data['districts']['data'][0]['name']);
        $this->assertEquals('Bagerhat', $data['districts']['data'][0]['display_name']);
    }
}