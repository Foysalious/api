<?php

namespace Tests\Feature\sDeliverOrderPlacement;

use Tests\Feature\FeatureTestCase;
use Throwable;

class OrderPlaceVendorAPITest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    /**
     * @throws Throwable
     */
    public function testGetDeliveryVendorList()
    {
        $response = $this->get('/v2/pos/delivery/vendor-list', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    /**
     * @throws Throwable
     */
    public function testGetSelfDeliveryVendorList()
    {
        $response = $this->get('/v2/pos/delivery/vendor-list', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals('নিজস্ব ডেলিভারি ', $data['data']['delivery_vendors'][0]['bn']);
        $this->assertEquals('Own Delivery', $data['data']['delivery_vendors'][0]['en']);
    }

    /**
     * @throws Throwable
     */
    public function testGetSdeliveryVendorList()
    {
        $response = $this->get('/v2/pos/delivery/vendor-list', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals('পেপারফ্লাই', $data['data']['delivery_vendors'][1]['bn']); //[1] represent API Arraylist
        $this->assertEquals('Paperfly', $data['data']['delivery_vendors'][1]['en']);
    }
}
