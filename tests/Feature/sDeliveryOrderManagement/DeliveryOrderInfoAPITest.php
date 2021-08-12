<?php


namespace Tests\Feature\sDeliveryOrderManagement;



use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInformation;
use Tests\Feature\FeatureTestCase;

class DeliveryOrderInfoAPITest extends FeatureTestCase
{
    /** @var $posOrderCreate */
    private $posOrderCreate;

    /** @var $partnerPosCustomer */
    private $partnerPosCustomer;

    /** @var $partnerDeliveryinfo */
    private $partnerDeliveryinfo;

    /** @var  $posOrderPayment */
    private $posOrderPayment;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTables([
            PosCustomer::class,
            PosOrder::class,
            PosOrderPayment::class,
            PartnerDeliveryInformation::class
        ]);
        $this->logIn();

        $this->partnerPosCustomer = factory(PosCustomer::class)->create();
        $this->posOrderCreate = factory(PosOrder::class)->create();
        $this->posOrderPayment = factory(PosOrderPayment::class)->create();
        $this->partnerDeliveryinfo = factory(PartnerDeliveryInformation::class)->create([
            'partner_id'=>$this->partner->id
        ]);
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