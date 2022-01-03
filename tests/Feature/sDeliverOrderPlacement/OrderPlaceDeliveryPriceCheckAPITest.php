<?php

namespace Tests\Feature\sDeliverOrderPlacement;

use Tests\Feature\FeatureTestCase;
use Throwable;

/**
 * @author Md Taufiqur Rahman Miraz <taufiqur.rahman@sheba.xyz>
 */
class OrderPlaceDeliveryPriceCheckAPITest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
    }

    /**
     * @throws Throwable
     */
    public function testSuccessfulResponseToFetchProductPriceAccordingToWeight()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'weight'            => '1',
            'cod_amount'        => 200,
            'delivery_district' => 'Dhaka',
            'delivery_thana'    => 'Gulshan',
            'partner_id'        => '1',
            'pickup_thana'      => 'Gulshan',
            "pickup_district"   => 'Dhaka',
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    /**
     * @throws Throwable
     */
    public function testDataFailedToPassValidationResponseToFetchProductPriceAccordingToWeight()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'cod_amount'        => 500,
            'delivery_district' => 'Dhaka',
            'delivery_thana'    => 'Dhanmondi',
            'partner_id'        => '1',
            'pickup_thana'      => 'Gulshan',
            "pickup_district"   => 'Dhaka',

        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The weight field is required.", $data['message']);
    }

    /**
     * @throws Throwable
     */
    public function testDataValidationResponseToFetchProductPriceWithoutCODAmount()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'weight'            => '1',
            'delivery_district' => 'Dhaka',
            'delivery_thana'    => 'Dhanmondi',
            'partner_id'        => '1',
            'pickup_thana'      => 'Gulshan',
            "pickup_district"   => 'Dhaka',
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The cod amount field is required.", $data['message']);
    }

    /**
     * @throws Throwable
     */
    public function testDataValidationResponseToFetchProductPriceWithoutPartnerId()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'weight'            => '1',
            'cod_amount'        => 500,
            'delivery_district' => 'Dhaka',
            'delivery_thana'    => 'Dhanmondi',
            'pickup_thana'      => 'Gulshan',
            "pickup_district"   => 'Dhaka',
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The partner id field is required.", $data['message']);
    }

    /**
     * @throws Throwable
     */
    public function testDataValidationResponseToFetchProductPriceWithoutDeliveryDistrictAndThana()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'weight'          => '1',
            'cod_amount'      => 500,
            'partner_id'      => '1',
            'pickup_thana'    => 'Gulshan',
            "pickup_district" => 'Dhaka',

        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals(
            "The delivery district field is required.The delivery thana field is required.",
            $data['message']
        );
    }

    /**
     * @throws Throwable
     */
    public function testProductPriceChangeAccordingToWeight()
    {
        $response = $this->post('/v2/pos/delivery/delivery-charge', [
            'weight'            => '1',
            'cod_amount'        => 500,
            'delivery_district' => 'Dhaka',
            'delivery_thana'    => 'Dhanmondi',
            'partner_id'        => '1',
            'pickup_thana'      => 'Gulshan',
            "pickup_district"   => 'Dhaka',
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(55, $data['delivery_charge']);
    }
}
