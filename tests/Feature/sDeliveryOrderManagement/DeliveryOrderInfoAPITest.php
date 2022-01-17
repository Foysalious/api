<?php

namespace Tests\Feature\sDeliveryOrderManagement;

use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInformation;
use Tests\Feature\FeatureTestCase;
use Throwable;

/**
 * @author Md Taufiqur Rahman Miraz <taufiqur.rahman@sheba.xyz>
 */
class DeliveryOrderInfoAPITest extends FeatureTestCase
{
    /** @var $posOrderCreate */
    private $posOrderCreate;
    /** @var $partnerPosCustomer */
    private $partnerPosCustomer;
    /** @var $partnerDeliveryinfo */
    private $partnerDeliveryinfo;
    /** @var $posOrderPayment */
    private $posOrderPayment;

    public function setUp(): void
    {
        parent::setUp();

        $this->truncateTables([
            PosCustomer::class,
            PosOrder::class,
            PosOrderPayment::class,
            PartnerDeliveryInformation::class,
        ]);
        $this->logIn();

        $this->partnerPosCustomer = PosCustomer::factory()->create();
        $this->posOrderCreate = PosOrder::factory()->create();
        $this->posOrderPayment = PosOrderPayment::factory()->create();
        $this->partnerDeliveryinfo = PartnerDeliveryInformation::factory()->create([
            'partner_id' => $this->partner->id,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testPosDeliveryOrderInfo()
    {
        $response = $this->get('/v2/pos/delivery/order-information/1', [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    /**
     * @throws Throwable
     */
    public function test401ResponseForPosDeliveryOrderInfo()
    {
        $response = $this->get('/v2/pos/delivery/order-information/1', [
            'Authorization' => "Bearer $this->token".'hrthew',
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }
}
