<?php

/**
 * Khairun Nahar
 * 22 May,2021
 */


namespace Tests\Feature\sDeliverOrderPlacement;


use App\Models\Partner;
use App\Models\PartnerPosService;
use App\Models\PosCategory;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Sheba\Partner\Delivery\DeliveryServerClient;
use PhpParser\Node\Expr\AssignOp\Mod;
use Sheba\Dal\PartnerDeliveryInformation\Model;
use App\Models\Profile;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockDeliveryServerClient;

class OrderPlacementAPITest extends FeatureTestCase
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
       $this->app->singleton(DeliveryServerClient::class,MockDeliveryServerClient::class);

    }

    public function testSuccessfulOrderPlaceAPIForPos()
    {
        $response = $this->post('/v2/pos/delivery/orders', [
            'logistic_partner_id' => 1,
            'weight' => '2.5',
            'cod_amount' => 500,
            'partner_name' => 'test',
            'partner_phone' => '01956154440',
            'pickup_address' => 'Dhanmondi',
            'pickup_thana' => 'Dhanmondi',
            "pickup_district"=> 'Dhanmondi',
            'customer_name' => 'Nawshin',
            'customer_phone' => '01620011019',
            'delivery_address' => 'bangla motor',
            'delivery_thana' => 'Ramna',
            'delivery_district' => 'Dhaka',
            'pos_order_id' => '1'

        ], [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        //dd($data );
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }


    public function testSuccessfulOrderPlaceAPIForSalesChannel()
    {
        $response = $this->post('/v2/pos/delivery/orders', [
            'logistic_partner_id' => 1,
            'weight' => '2.5',
            'cod_amount' => 500,
            'partner_name' => 'Test',
            'partner_phone' => '01956154440',
            'pickup_address' => 'Dhanmondi',
            'pickup_thana' => 'Dhanmondi',
            "pickup_district"=> 'Dhanmondi',
            'customer_name' => 'Nawshin',
            'customer_phone' => '01620011019',
            'delivery_address' => 'bangla motor',
            'delivery_thana' => 'Ramna',
            'delivery_district' => 'Dhaka',
            'pos_order_id' => '1'

        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
       // dd($data);
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    public function testPosOrderPlaceAPIForDataFailedToPassValidation()
    {
        $response = $this->post('/v2/pos/delivery/orders', [
            /*'weight' => '2.5',
            'cod_amount' => 500,
            'partner_name' => 'Test',
            'partner_phone' => '01956154440',
            'pickup_address' => 'Dhanmondi',
            'pickup_thana' => 'Dhanmondi',
            "pickup_district"=> 'Dhanmondi',
            'customer_name' => 'Nawshin',
            'customer_phone' => '01620011019',
            'delivery_address' => 'bangla motor',
            'delivery_thana' => 'Ramna',
            'delivery_district' => 'Dhaka',
            'pos_order_id' => '1'*/

        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);

        $this->assertEquals(400, $data['code']);
    }

    public function testPosOrderPlaceAPIForAuthorizationError()
    {
        $response = $this->post('/v2/pos/delivery/orders', [
            'logistic_partner_id' => 1,
            'weight' => '2.5',
            'cod_amount' => 500,
            'partner_name' => 'Test',
            'partner_phone' => '01956154440',
            'pickup_address' => 'Dhanmondi',
            'pickup_thana' => 'Dhanmondi',
            "pickup_district"=> 'Dhanmondi',
            'customer_name' => 'Nawshin',
            'customer_phone' => '01620011019',
            'delivery_address' => 'bangla motor',
            'delivery_thana' => 'Ramna',
            'delivery_district' => 'Dhaka',
            'pos_order_id' => '1'

        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(401, $data['code']);
        $this->assertEquals("Your session has expired. Try Login", $data['message']);
    }

    public function testSuccessfullyDataInsertIntoDBForPosOrderPlaceAPI()
    {
        $response = $this->post('/v2/pos/delivery/orders', [
            'logistic_partner_id' => 1,
            'weight' => '2.5',
            'cod_amount' => 500,
            'partner_name' => 'Test',
            'partner_phone' => '01956154440',
            'pickup_address' => 'Dhanmondi',
            'pickup_thana' => 'Dhanmondi',
            "pickup_district"=> 'Dhanmondi',
            'customer_name' => 'Nawshin',
            'customer_phone' => '01620011019',
            'delivery_address' => 'bangla motor',
            'delivery_thana' => 'Ramna',
            'delivery_district' => 'Dhaka',
            'pos_order_id' => '1'

        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $Pos_order=PosOrder::first();

        $this->assertEquals(1,$Pos_order->partner_wise_order_id);
        $this->assertEquals(1,$Pos_order->partner_id);
        $this->assertEquals(1,$Pos_order->customer_id);
        $this->assertEquals('Due',$Pos_order->payment_status);
        $this->assertEquals('3580.00',$Pos_order->delivery_charge);
        $this->assertEquals(1,$Pos_order->delivery_vendor_name);
        $this->assertEquals('ORD-1616491561-0016',$Pos_order->delivery_request_id);
        $this->assertEquals('Ramna',$Pos_order->delivery_thana);
        $this->assertEquals('Dhaka',$Pos_order->delivery_district);
        $this->assertEquals('Created',$Pos_order->delivery_status);
        $this->assertEquals('Pending',$Pos_order->status);
        $this->assertEquals('pos',$Pos_order->sales_channel);
    }

    public function testPosOrderPlaceAPIForSuccessfullyDataInsertIntoDBForWebstore()
    {
        $pos_order = Profile::find(1);;
        $pos_order->update(["sales_channel" => 'web store']);

        $response = $this->post('/v2/pos/delivery/orders', [
            'logistic_partner_id' => 1,
            'weight' => '2.5',
            'cod_amount' => 500,
            'partner_name' => 'Test',
            'partner_phone' => '01956154440',
            'pickup_address' => 'Dhanmondi',
            'pickup_thana' => 'Dhanmondi',
            "pickup_district"=> 'Dhanmondi',
            'customer_name' => 'Nawshin',
            'customer_phone' => '01620011019',
            'delivery_address' => 'bangla motor',
            'delivery_thana' => 'Ramna',
            'delivery_district' => 'Dhaka',
            'pos_order_id' => '1'

        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $Pos_order=PosOrder::first();

        $this->assertEquals(1,$Pos_order->partner_wise_order_id);
        $this->assertEquals(1,$Pos_order->partner_id);
        $this->assertEquals(1,$Pos_order->customer_id);
        $this->assertEquals('Due',$Pos_order->payment_status);
        $this->assertEquals('3580.00',$Pos_order->delivery_charge);
        $this->assertEquals(1,$Pos_order->delivery_vendor_name);
        $this->assertEquals('ORD-1616491561-0016',$Pos_order->delivery_request_id);
        $this->assertEquals('Ramna',$Pos_order->delivery_thana);
        $this->assertEquals('Dhaka',$Pos_order->delivery_district);
        $this->assertEquals('Created',$Pos_order->delivery_status);
        $this->assertEquals('Pending',$Pos_order->status);
        $this->assertEquals('pos',$Pos_order->sales_channel);
    }



}