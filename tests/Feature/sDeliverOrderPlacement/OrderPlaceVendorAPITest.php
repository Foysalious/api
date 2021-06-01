<?php

/**
 * Khairun Nahar
 * 22 May,2021
 */


namespace Tests\Feature\sDeliverOrderPlacement;


use Tests\Feature\FeatureTestCase;

class OrderPlaceVendorAPITest extends FeatureTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->logIn();


    }


    public function testGetDeliveryVendorList()
    {

        $response = $this->get('/v2/pos/delivery/vendor-list',[
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);

    }

    public function testGetSelfDeliveryVendorList()
    {

        $response = $this->get('/v2/pos/delivery/vendor-list',[
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals('নিজস্ব ডেলিভারি ', $data['data']['delivery_vendors'][0]['bn']);
        $this->assertEquals('Own Delivery', $data['data']['delivery_vendors'][0]['en']);

    }

    public function testGetSdeliveryVendorList()
    {

        $response = $this->get('/v2/pos/delivery/vendor-list',[
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals('পেপারফ্লাই', $data['data']['delivery_vendors'][1]['bn']); //[1] represent API Arraylist
        $this->assertEquals('Paperfly', $data['data']['delivery_vendors'][1]['en']);

    }

}