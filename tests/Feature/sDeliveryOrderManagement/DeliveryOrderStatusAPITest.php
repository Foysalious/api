<?php namespace Tests\Feature\sDeliveryOrderManagement;

/**
 * Khairun
 * 25th May, 2021
 */

use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Sheba\Partner\Delivery\DeliveryServerClient;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockDeliveryServerClient;

/**
 * @author Md Taufiqur Rahman Miraz <taufiqur.rahman@sheba.xyz>
 */
class DeliveryOrderStatusAPITest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();

       $this->partnerPosCustomer = factory(PosCustomer::class)->create();
       $this->posOrderCreate = factory(PosOrder::class)->create();
       $this->app->singleton(DeliveryServerClient::class,MockDeliveryServerClient::class);
    }

    public function testGetDeliveryOrderStatusUpdate()
    {
        $response = $this->get('/v2/pos/delivery/delivery-status?pos_order_id=1',  [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    public function testDeliveryOrderStatusForAuthorizationError()
    {
        $response = $this->get('/v2/pos/delivery/delivery-status?pos_order_id=1');
        $data = $response->decodeResponseJson();
        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }

    public function testDeliveryOrderCreatedStatusUpdate()
    {
        $response = $this->get('/v2/pos/delivery/delivery-status?pos_order_id=1',  [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals("Created", $data['status']);
    }

    /**
     * Can't Mock those delivery Status
     * according to sDelivery API doc they only provide
     * 200-> Created Order Status Update
     */
/*
    public function testDeliveryOrderPickupStatusUpdate()
    {

        $response = $this->get('/v2/pos/delivery/delivery-status?pos_order_id=1',  [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals("Picked up", $data['status']);

    }

    public function testDeliveryOrderDeliveredStatusUpdate()
    {

        $response = $this->get('/v2/pos/delivery/delivery-status?pos_order_id=1',  [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals("Delivered", $data['status']);

    }

    public function testDeliveryOrderReturnedStatusUpdate()
    {

        $response = $this->get('/v2/pos/delivery/delivery-status?pos_order_id=1',  [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals("Returned", $data['status']);

    }

    public function testDeliveryOrderPartialStatusUpdate()
    {

        $response = $this->get('/v2/pos/delivery/delivery-status?pos_order_id=1',  [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals("Partial", $data['status']);

    }


    public function testDeliveryOrderCloseStatusUpdate()
    {

        $response = $this->get('/v2/pos/delivery/delivery-status?pos_order_id=1',  [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals("Close", $data['status']);

    }*/
}
