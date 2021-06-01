<?php

/**
 * Khairun
 * 26th May, 2021
 */


namespace Tests\Feature\sDeliveryOrderManagement;


use App\Models\Partner;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Sheba\Partner\Delivery\DeliveryServerClient;
use Sheba\Dal\PartnerDeliveryInformation\Model;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockDeliveryServerClient;

class DeliveryVendorUpdateAPITest extends FeatureTestCase
{

    private $partnerDeliveryinfo;


    public function setUp()
    {
        parent::setUp();

        $this->truncateTables([
            Model::class
        ]);
        $this->logIn();
        $this->partnerDeliveryinfo = factory(Model::class)->create([
            'partner_id'=> $this->partner->id
            ]);
        $this->app->singleton(DeliveryServerClient::class,MockDeliveryServerClient::class);


    }

    public function testsDeliveryUpdateVendor()
    {
        $response = $this->post('/v2/pos/delivery/partner-vendor', [
            'vendor_name' => 'paperfly',
            'delivery_info_id' => 1

        ], [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    public function testVendorSelectSelfDeliverySystem()
    {
        $response = $this->post('/v2/pos/delivery/partner-vendor', [
            'vendor_name' => 'own_delivery',
            'delivery_info_id' => 2

        ], [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    public function testAuthorizationError()
    {
        $response = $this->post('/v2/pos/delivery/partner-vendor', [
            'vendor_name' => 'own_delivery',
            'delivery_info_id' => 2

        ], [
            'Authorization' => "Bearer $this->token"."behvbeh"
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }

    public function testDataValidationError()
    {
        $response = $this->post('/v2/pos/delivery/partner-vendor', [
            'vendor_name' => 'Own_delivery',
            'delivery_info_id' => 2

        ], [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }

    public function testDeliveryVendorDataSuccessfullyUpdateIntoDB()
    {
        $response = $this->post('/v2/pos/delivery/partner-vendor', [
            'vendor_name' => 'paperfly',
            'delivery_info_id' => 1

        ], [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        $Partner_delivery_information=Model::first();

        $this->assertEquals('paperfly',$Partner_delivery_information->delivery_vendor);
    }

    public function testSeltDeliverySuccessfullyUpdateIntoDB()
    {
        $response = $this->post('/v2/pos/delivery/partner-vendor', [
            'vendor_name' => 'own_delivery',
            'delivery_info_id' => 2

        ], [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        $Partner_delivery_information=Model::first();

        $this->assertEquals('own_delivery',$Partner_delivery_information->delivery_vendor);
    }


}