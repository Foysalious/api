<?php


namespace Tests\Feature\sDeliveryOrderManagement;


use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Sheba\Partner\Delivery\DeliveryServerClient;
use Sheba\Dal\PartnerDeliveryInformation\Model;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockDeliveryServerClient;

class DeliveryOrderCancelAPITest extends FeatureTestCase
{

    private $posOrderCreate;
    private $partnerPosCustomer;


    public function setUp()
    {
        parent::setUp();

        $this->truncateTables([
            PosOrder::class,
            PosCustomer::class,

        ]);
        $this->logIn();

        $this->partnerPosCustomer = factory(PosCustomer::class)->create();
        $this->posOrderCreate = factory(PosOrder::class)->create();
        $this->app->singleton(DeliveryServerClient::class, MockDeliveryServerClient::class);

    }

    public function testCancelPosDeliveryOrder()
    {
        $response = $this->post('/v2/pos/delivery/cancel-order', [
            'pos_order_id' => 1

        ], [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals("ডেলিভারি অর্ডারটি বাতিল করা হয়েছে", $data['messages']);
    }

    public function testFailedToCancelPosDeliveryOrderDueToAuthorizationError()
    {
        $response = $this->post('/v2/pos/delivery/cancel-order', [
            'pos_order_id' => 1

        ], [
            'Authorization' => "Bearer $this->token".'rtthrthr'
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }

    public function testPosOrderIdFieldIsRequired()
    {
        $response = $this->post('/v2/pos/delivery/cancel-order', [

        ], [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The pos order id field is required.", $data['message']);
    }

    /**
     * Due to sDelivery Server dependency the Pos
     * order status will remain "Pending"
     */

    public function testCancelOrderDataUpdateIntoDB()
    {
        $response = $this->post('/v2/pos/delivery/cancel-order', [
            'pos_order_id' => 1

        ], [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        $Cancel_order = PosOrder::first();
        $this->assertEquals("Cancelled", $Cancel_order->status);
    }

}