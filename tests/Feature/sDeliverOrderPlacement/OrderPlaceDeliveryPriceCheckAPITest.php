<?php

/**
 * Khairun Nahar
 * 22 May,2021
 */


namespace Tests\Feature\sDeliverOrderPlacement;


use App\Models\PosCustomer;
use App\Models\PosOrder;
use Tests\Feature\FeatureTestCase;

class OrderPlaceDeliveryPriceCheckAPITest extends FeatureTestCase
{


    public function setUp()
    {
        parent::setUp();

        $this->logIn();


    }

    public function testSuccessfulResponseToFetchProductPriceAccordingtoWeight()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'weight' => '1',
            'cod_amount' => 500,
            'delivery_district' => 'Dhaka',
            'delivery_thana' => 'Dhanmondi',
            'partner_id' => '1',
            'pickup_thana' => 'Dhaka',
            "pickup_district"=> 'Gulshan'

        ]/*, [
            'Authorization' => "Bearer $this->token"
        ]*/);
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    public function testDataFailedToPassValidationResponseToFetchProductPriceAccordingtoWeight()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'cod_amount' => 500,
            'delivery_district' => 'Dhaka',
            'delivery_thana' => 'Dhanmondi',
            'partner_id' => '1',
            'pickup_thana' => 'Dhaka',
            "pickup_district"=> 'Gulshan'

        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The weight field is required.", $data['message']);
    }

    public function testDatasValidationResponseToFetchProductPriceWithoutCODAmount()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'weight' => '1',
            'delivery_district' => 'Dhaka',
            'delivery_thana' => 'Dhanmondi',
            'partner_id' => '1',
            'pickup_thana' => 'Dhaka',
            "pickup_district"=> 'Gulshan'

        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The cod amount field is required.", $data['message']);
    }

    public function testDatasValidationResponseToFetchProductPriceWithoutPartnerId()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'weight' => '1',
            'cod_amount' => 500,
            'delivery_district' => 'Dhaka',
            'delivery_thana' => 'Dhanmondi',
            'pickup_thana' => 'Dhaka',
            "pickup_district"=> 'Gulshan'

        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The partner id field is required.", $data['message']);
    }

    public function testDatasValidationResponseToFetchProductPriceWithoutDeliveryDistrictandThana()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'weight' => '1',
            'cod_amount' => 500,
            'partner_id' => '1',
            'pickup_thana' => 'Dhaka',
            "pickup_district"=> 'Gulshan'

        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The delivery district field is required.The delivery thana field is required.", $data['message']);
    }

  /*  public function testFetchProductPriceAccordingtoWeight()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'weight' => '1',
            'cod_amount' => 500,
            'delivery_district' => 'Dhaka',
            'delivery_thana' => 'Dhanmondi',
            'partner_id' => '1',
            'pickup_thana' => 'Dhaka',
            "pickup_district"=> 'Gulshan'

        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(1, $data['info']['data'][0]['id']);
        $this->assertEquals(125, $data['info']['data'][0]['package_price']);
        $this->assertEquals('user@paperfly.com', $data['info']['data'][0]['email']);
        $this->assertEquals('01700112233', $data['info']['data'][0]['phone']);

    }*/

    public function testProductPriceChangeAccordingtoWeight()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'weight' => '5',
            'cod_amount' => 500,
            'delivery_district' => 'Dhaka',
            'delivery_thana' => 'Dhanmondi',
            'partner_id' => '1',
            'pickup_thana' => 'Dhaka',
            "pickup_district"=> 'Gulshan'

        ]);
        $data = $response->decodeResponseJson();
        //dd($data);

        $this->assertEquals(240, $data['delivery_charge']);

    }
}