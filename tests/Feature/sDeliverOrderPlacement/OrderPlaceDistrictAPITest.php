<?php

namespace Tests\Feature\sDeliverOrderPlacement;

use Tests\Feature\FeatureTestCase;
use Throwable;

/**
 * @author Md Taufiqur Rahman Miraz <taufiqur.rahman@sheba.xyz>
 */
class OrderPlaceDistrictAPITest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    /**
     * @throws Throwable
     */
    public function testGetDeliveryAddressDistrict()
    {
        $response = $this->get('/v2/pos/delivery/district');
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    /**
     * @throws Throwable
     */
    public function testFetchListGetDeliveryAddressDistrict()
    {
        $response = $this->get('/v2/pos/delivery/district');
        $data = $response->decodeResponseJson();

        $this->assertEquals('Bagerhat', $data['districts']['data'][0]['name']);
        $this->assertEquals('Bagerhat', $data['districts']['data'][0]['display_name']);
    }
}
