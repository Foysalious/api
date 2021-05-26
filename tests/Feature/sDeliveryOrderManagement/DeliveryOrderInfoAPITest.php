<?php


namespace Tests\Feature\sDeliveryOrderManagement;


use App\Models\Customer;
use App\Models\Partner;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Models\Profile;
use App\Sheba\Partner\Delivery\DeliveryServerClient;
use Sheba\Dal\PartnerDeliveryInformation\Model;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockDeliveryServerClient;

class DeliveryOrderInfoAPITest extends FeatureTestCase
{

    private $posOrderCreate;
    private $partnerPosCustomer;
    private $partnerDeliveryinfo;
    private $posOrderPayment;


    public function setUp()
    {
        parent::setUp();

        $this->truncateTables([
            Model::class,
            PosCustomer::class,
            PosOrder::class,
            PosOrderPayment::class
        ]);
        $this->logIn();

        $this->partnerDeliveryinfo = factory(Model::class)->create();
        $this->partnerPosCustomer = factory(PosCustomer::class)->create();
        $this->posOrderCreate = factory(PosOrder::class)->create();
        $this->posOrderPayment = factory(PosOrderPayment::class)->create();

    }

    public function testPosDeliveryOrderInfo()
    {
        $response = $this->get('/v2/pos/delivery/order-information/1', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    public function test401ResponseforPosDeliveryOrderInfo()
    {
        $response = $this->get('/v2/pos/delivery/order-information/1', [
            'Authorization' => "Bearer $this->token" . 'hrthew'
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }

/*    public function testPosDeliveryOrderCODAmountcalculate()
    {
        $response = $this->get('/v2/pos/delivery/order-information/1', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals('0', $data['order_information'][0]['cod_amount'][]);

    }*/
}