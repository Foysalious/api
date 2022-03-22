<?php

namespace Tests\Feature\CustomerOrder\OrderDetails;

use App\Models\Job;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\Promotion;
use App\Models\Voucher;
use Tests\Feature\FeatureTestCase;

class AddPromoTest extends FeatureTestCase
{
    private $voucher;
    protected $job;

    public function setUp() :void
    {
        parent::setUp();
        $this->logIn();
        $this->truncateTables([
            Order::class,
            Job::class,
            Voucher::class,
            Promotion::class,
            PartnerOrder::class,
        ]);
        $this->voucher = Voucher::factory()->create();
        $this->order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'location_id' => 4,
            'sales_channel' => 'app',
//            'voucher_id' => $this->voucher->id
        ]);

//        factory(Promotion::class)->create([
//            'customer_id' =>$this->customer->id,
//            'voucher_id' => $this->voucher->id,
//            'is_valid' =>1,
//            'valid_till'=>$this -> voucher -> end_date
//        ]);

        $this->partner_order = PartnerOrder::factory()->create([
            'order_id' => $this->order->id,
            'discount' => 0
        ]);

        $this->job = Job::factory()->create([
            'partner_order_id' => $this->partner_order->id
        ]);
    }

    public function test_API_Giving_200_For_Valid_Input_XYZ()
    {
        $response = $this->post("v2/customers/" . $this->customer->id . "/jobs/" . $this->job->id . "/promotions",
            [
                "code" => $this->voucher->code,
                "sales_channel" => "app"
            ],
            [
                'Authorization' => "Bearer $this->token"
            ]);
        Promotion::factory()->create([
            'customer_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'is_valid' => 1,
            'valid_till' => $this->voucher->end_date
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);

    }

    public function test_API_Giving_403_For_Invalid_Code()
    {
        $response = $this->post("v2/customers/" . $this->customer->id . "/jobs/" . $this->job->id . "/promotions",
            [
                "code" => 123,
                "sales_channel" => "app"
            ],
            [
                'Authorization' => "Bearer $this->token"
            ]);

//        factory(Promotion::class)->create([
//            'customer_id' =>$this->customer->id,
//            'voucher_id' => $this->voucher->id,
//            'is_valid' =>1,
//            'valid_till'=>$this -> voucher -> end_date
//        ]);

        $data = $response->decodeResponseJson();
        dd($data);
        $this->assertEquals(403, $data['code']);
    }

    public function test_API_Giving_403_For_Invalid_Date()
    {
        $response = $this->post("v2/customers/" . $this->customer->id . "/jobs/" . $this->job->id . "/promotions",
            [
                "code" => $this->voucher->code,
                "sales_channel" => "app"
            ],
            [
                'Authorization' => "Bearer $this->token"
            ]);

        Promotion::factory()->create([
            'customer_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'is_valid' => 1,
            'valid_till' => $this->voucher->end_date
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data['code']);

    }

    public function test_API_Giving_403_If_Inactive()
    {
        $response = $this->post("v2/customers/" . $this->customer->id . "/jobs/" . $this->job->id . "/promotions",
            [
                "code" => $this->voucher->code,
                "sales_channel" => "app"
            ],
            [
                'Authorization' => "Bearer $this->token"
            ]);

        Promotion::factory()->create([
            'customer_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'is_valid' => 1,
            'valid_till' => $this->voucher->end_date
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data['code']);

    }

    public function test_Response_403_if_Promo_used_already()
    {

        $this->truncateTables([
            Order::class,
            JOb::class,
            Voucher::class,
            Promotion::class,
            PartnerOrder::class,
        ]);

        $this->voucher = Voucher::factory()->create();
        $this->order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'location_id' => 4,
            'sales_channel' => 'app',
            'voucher_id' => $this->voucher->id
        ]);
        $this->partner_order = PartnerOrder::factory()->create([
            'order_id' => $this->order->id,
            'discount' => 0
        ]);

        $this->job = Job::factory()->create([
            'partner_order_id' => $this->partner_order->id
        ]);
        Promotion::factory()->create([
            'customer_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'is_valid' => 0,
            'valid_till' => $this->voucher->end_date
        ]);
        $response = $this->post("v2/customers/" . $this->customer->id . "/jobs/" . $this->job->id . "/promotions",
            [
                "code" => $this->voucher->code,
                "sales_channel" => "app"
            ],
            [
                'Authorization' => "Bearer $this->token"
            ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
    }

}