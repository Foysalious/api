<?php

namespace Tests\Feature\sDeliverOrderPlacement;

use App\Models\PartnerPosCustomer;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Sheba\Partner\Delivery\DeliveryServerClient;
use App\Models\Profile;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockDeliveryServerClient;
use Throwable;

/**
 * @author Md Taufiqur Rahman Miraz <taufiqur.rahman@sheba.xyz>
 */
class OrderPlacementAPITest extends FeatureTestCase
{
    /** @var $posOrderCreate */
    private $posOrderCreate;

    /** @var PartnerPosCustomer $partnerPosCustomer */
    private $partnerPosCustomer;

    public function setUp(): void
    {
        parent::setUp();

        $this->truncateTables([
            PosOrder::class,
            PosCustomer::class,
        ]);
        $this->logIn();

        $this->partnerPosCustomer = PosCustomer::factory()->create();
        $this->posOrderCreate = PosOrder::factory()->create();

        $this->app->singleton(DeliveryServerClient::class, MockDeliveryServerClient::class);
    }

    /**
     * @throws Throwable
     */
    public function testSuccessfulOrderPlaceAPIForPos()
    {
        $response = $this->post('/v2/pos/delivery/orders', [
            'logistic_partner_id' => 1,
            'weight'              => '2.5',
            'cod_amount'          => 500,
            'partner_name'        => 'test',
            'partner_phone'       => '01956154440',
            'pickup_address'      => 'Dhanmondi',
            'pickup_thana'        => 'Dhanmondi',
            "pickup_district"     => 'Dhanmondi',
            'customer_name'       => 'Nawshin',
            'customer_phone'      => '01620011019',
            'delivery_address'    => 'bangla motor',
            'delivery_thana'      => 'Ramna',
            'delivery_district'   => 'Dhaka',
            'pos_order_id'        => '1',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    /**
     * @throws Throwable
     */
    public function testSuccessfulOrderPlaceAPIForSalesChannel()
    {
        $response = $this->post('/v2/pos/delivery/orders', [
            'logistic_partner_id' => 1,
            'weight'              => '2.5',
            'cod_amount'          => 500,
            'partner_name'        => 'Test',
            'partner_phone'       => '01956154440',
            'pickup_address'      => 'Dhanmondi',
            'pickup_thana'        => 'Dhanmondi',
            "pickup_district"     => 'Dhanmondi',
            'customer_name'       => 'Nawshin',
            'customer_phone'      => '01620011019',
            'delivery_address'    => 'bangla motor',
            'delivery_thana'      => 'Ramna',
            'delivery_district'   => 'Dhaka',
            'pos_order_id'        => '1',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    /**
     * @throws Throwable
     */
    public function testPosOrderPlaceAPIForDataFailedToPassValidation()
    {
        $response = $this->post('/v2/pos/delivery/orders', [
            'weight'            => '2.5',
            'cod_amount'        => 500,
            'partner_name'      => 'Test',
            'partner_phone'     => '01956154440',
            'pickup_address'    => 'Dhanmondi',
            'pickup_thana'      => 'Dhanmondi',
            "pickup_district"   => 'Dhanmondi',
            'customer_name'     => 'Nawshin',
            'customer_phone'    => '01620011019',
            'delivery_address'  => 'bangla motor',
            'delivery_district' => 'Dhaka',
            'pos_order_id'      => '1',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
    }

    /**
     * @throws Throwable
     */
    public function testPosOrderPlaceAPIForAuthorizationError()
    {
        $response = $this->post('/v2/pos/delivery/orders', [
            'logistic_partner_id' => 1,
            'weight'              => '2.5',
            'cod_amount'          => 500,
            'partner_name'        => 'Test',
            'partner_phone'       => '01956154440',
            'pickup_address'      => 'Dhanmondi',
            'pickup_thana'        => 'Dhanmondi',
            "pickup_district"     => 'Dhanmondi',
            'customer_name'       => 'Nawshin',
            'customer_phone'      => '01620011019',
            'delivery_address'    => 'bangla motor',
            'delivery_thana'      => 'Ramna',
            'delivery_district'   => 'Dhaka',
            'pos_order_id'        => '1',
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }

    /**
     * @throws Throwable
     */
    public function testSuccessfullyDataInsertIntoDBForPosOrderPlaceAPI()
    {
        $response = $this->post('/v2/pos/delivery/orders', [
            'logistic_partner_id' => 1,
            'weight'              => '2.5',
            'cod_amount'          => 500,
            'partner_name'        => 'Test',
            'partner_phone'       => '01956154440',
            'pickup_address'      => 'Dhanmondi',
            'pickup_thana'        => 'Dhanmondi',
            "pickup_district"     => 'Dhanmondi',
            'customer_name'       => 'Nawshin',
            'customer_phone'      => '01620011019',
            'delivery_address'    => 'bangla motor',
            'delivery_thana'      => 'Ramna',
            'delivery_district'   => 'Dhaka',
            'pos_order_id'        => '1',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->decodeResponseJson();

        $Pos_order = PosOrder::first();
        $this->assertEquals(1, $Pos_order->partner_wise_order_id);
        $this->assertEquals(1, $Pos_order->partner_id);
        $this->assertEquals(1, $Pos_order->customer_id);
        $this->assertEquals('Due', $Pos_order->payment_status);
        $this->assertEquals('50.00', $Pos_order->delivery_charge);
        $this->assertEquals(1, $Pos_order->delivery_vendor_name);
        $this->assertEquals('ORD-1616491561-0016', $Pos_order->delivery_request_id);
        $this->assertEquals('Ramna', $Pos_order->delivery_thana);
        $this->assertEquals('Dhaka', $Pos_order->delivery_district);
        $this->assertEquals('Created', $Pos_order->delivery_status);
        // Need to mock the status, otherwise status will be change
        // $this->assertEquals('Shipped',$Pos_order->status);
        $this->assertEquals('pos', $Pos_order->sales_channel);
    }

    /**
     * @throws Throwable
     */
    public function testPosOrderPlaceAPIForSuccessfullyDataInsertIntoDBForWebstore()
    {
        Profile::find(1)->update(["sales_channel" => 'web store']);
        $response = $this->post('/v2/pos/delivery/orders', [
            'logistic_partner_id' => 1,
            'weight'              => '2.5',
            'cod_amount'          => 500,
            'partner_name'        => 'Test',
            'partner_phone'       => '01956154440',
            'pickup_address'      => 'Dhanmondi',
            'pickup_thana'        => 'Dhanmondi',
            "pickup_district"     => 'Dhanmondi',
            'customer_name'       => 'Nawshin',
            'customer_phone'      => '01620011019',
            'delivery_address'    => 'bangla motor',
            'delivery_thana'      => 'Ramna',
            'delivery_district'   => 'Dhaka',
            'pos_order_id'        => '1',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);

        $response->decodeResponseJson();

        $Pos_order = PosOrder::first();

        $this->assertEquals(1, $Pos_order->partner_wise_order_id);
        $this->assertEquals(1, $Pos_order->partner_id);
        $this->assertEquals(1, $Pos_order->customer_id);
        $this->assertEquals('Due', $Pos_order->payment_status);
        $this->assertEquals('50.00', $Pos_order->delivery_charge);
        $this->assertEquals(1, $Pos_order->delivery_vendor_name);
        $this->assertEquals('ORD-1616491561-0016', $Pos_order->delivery_request_id);
        $this->assertEquals('Ramna', $Pos_order->delivery_thana);
        $this->assertEquals('Dhaka', $Pos_order->delivery_district);
        $this->assertEquals('Created', $Pos_order->delivery_status);
        $this->assertEquals('pos', $Pos_order->sales_channel);
    }
}
