<?php


namespace Tests\Feature\CustomerOrder\OrderPlace;

use App\Models\Job;
use App\Models\Location;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\Promotion;
use App\Models\Voucher;
use Carbon\Carbon;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Tests\Feature\FeatureTestCase;
use function Tests\Feature\CustomerOrder\MxOrderPlace\factory;


class AddVoucherTest extends FeatureTestCase

{

    public function test_AddVoucher_API_Giving_200_For_Valid_Input()

    {
        parent::setUp();

        $this->logIn();

        $this->location = Location::find(4);

        $this->masterCategory = factory(Category::class)->create();
        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $this->masterCategory->id
        ]);
        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id' => $this->service->id,
            'prices' => 2000
        ]);
        $this->voucher = factory(Voucher::class)->create();


        $response = $this->post("v3/customers/1/orders/promotions/add",
            [
                "services" => json_encode([[
                    "id" => $this->service->id,
                    "option" => [0],
                    "quantity" => 5
                ]]),
                "location" => 4,
                "sales_channel" => "App",
                "date" => Carbon::today(),
                "time" => "14:00:00-15:00:00",
                "code" => $this->voucher->code
            ],
            [
                'Authorization' => "Bearer $this->token"
            ]
        );
        factory(Promotion::class)->create([
            'customer_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'is_valid' => 1,
            'valid_till' => $this->voucher->end_date
        ]);

        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->asserttrue(count($data['promotion']) > 0, $data['promotion']);
        $this->assertEquals($this->voucher->amount, $data['promotion']['amount']);
        $this->assertEquals($this->voucher->code, $data['promotion']['code']);
        $this->assertEquals($this->voucher->id, $data['promotion']['id']);
        $this->assertEquals($this->voucher->title, $data['promotion']['title']);

//       dd($data['promotion'][0]);
//       dd(gettype($data['code']));
//        $this -> assertEquals($this->voucher->id, $data['promotion'][2]['amount']);
    }

    public function testAddVoucherAPIGiving405ForWrongMethod()
    {
        parent::setUp();


        $this->logIn();
        $this->location = Location::find(4);

        $this->masterCategory = factory(Category::class)->create();
        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $this->masterCategory->id
        ]);
        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id' => $this->service->id,
            'prices' => 2000
        ]);
        $this->voucher = factory(Voucher::class)->create();

        $response = $this->get("v3/customers/1/orders/promotions/add",
            [
                "services" => json_encode([[
                    "id" => $this->service->id,
                    "option" => [0],
                    "quantity" => 5
                ]]),
                "location" => 4,
                "sales_channel" => "App",
                "date" => Carbon::today(),
                "time" => "14:00:00-15:00:00",
                "code" => $this->voucher->code
            ],
            [
                'Authorization' => "Bearer $this->token"
            ]
        );
        factory(Promotion::class)->create([
            'customer_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'is_valid' => 1,
            'valid_till' => $this->voucher->end_date
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals("405 Method Not Allowed", $data['message']);
    }

    public function test_For_Promo_Time_Invalid_Response_Giving_403()

    {
        parent::setUp();


        $this->logIn();
        $this->location = Location::find(4);

        $this->masterCategory = factory(Category::class)->create();
        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $this->masterCategory->id
        ]);
        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id' => $this->service->id,
            'prices' => 2000
        ]);

        $this->voucher = factory(Voucher::class)->create([
            'start_date' => 2021 - 01 - 01,
            'end_date' => 2021 - 01 - 02
        ]);

        $response = $this->post("v3/customers/1/orders/promotions/add",
            [
                "services" => json_encode([[
                    "id" => $this->service->id,
                    "option" => [0],
                    "quantity" => 5
                ]]),
                "location" => 4,
                "sales_channel" => "App",
                "date" => Carbon::today(),
                "code" => $this->voucher->code
            ],
            [
                'Authorization' => "Bearer $this->token"
            ]
        );
        factory(Promotion::class)->create([
            'customer_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'is_valid' => 1,
            'valid_till' => $this->voucher->end_date
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(403, $data['code']);
        $this->AssertEquals("This code is not valid. (Time Over)", $data["message"]);
    }

    public function test_Response_403_For_Already_Used_Voucher()
    {


        parent::setUp();


        $this->logIn();
        $this->location = Location::find(4);

        $this->masterCategory = factory(Category::class)->create();
        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $this->masterCategory->id
        ]);
        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id' => $this->service->id,
            'prices' => 2000
        ]);

        /*
         * is_active = 0
         */

        $this->voucher = factory(Voucher::class)->create([
            'is_active' => '0',
            'start_date' => 2021 - 01 - 01,
            'end_date' => 2021 - 01 - 02,
            'max_order' => '1'
        ]);
        $this->promotion = factory(Promotion::class)->create([
            'customer_id' => $this->customer->id,
            'voucher_id' => $this->voucher->id,
            'is_valid' => 1,
            'valid_till' => $this->voucher->end_date
        ]);

        $this->order = factory(Order::class)->create([
            'customer_id' => $this->customer->id,
            'sales_channel' => 'app',
            'location_id' => 4,
            'voucher_id' => $this->voucher->id
        ]);

        $this->partner_order = factory(PartnerOrder::class)->create([
            'order_id' => $this->order->id
        ]);

        $this->job = factory(Job::class)->create([
            'partner_order_id' => $this->partner_order->id

        ]);

        $response = $this->post("v3/customers/1/orders/promotions/add",
            [
                "services" => json_encode([[
                    "id" => $this->service->id,
                    "option" => [0],
                    "quantity" => 5
                ]]),
                "location" => 4,
                "sales_channel" => "App",
                "date" => Carbon::today(),
                "code" => $this->voucher->code
            ],
            [
                'Authorization' => "Bearer $this->token"
            ]
        );

        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("This code is not valid. (For you) ()", $data["message"]);
    }

    public function test_Response_403_without_voucher()
    {


        $this->logIn();
        $this->location = Location::find(4);

        $this->masterCategory = factory(Category::class)->create();
        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $this->masterCategory->id
        ]);
        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id' => $this->service->id,
            'prices' => 2000
        ]);

        $this->voucher = factory(Voucher::class)->create([]);
//POST WITHOUT VOUCHER
        $response = $this->post("v3/customers/1/orders/promotions/add",
            [
                "services" => json_encode([[
                    "id" => $this->service->id,
                    "option" => [0],
                    "quantity" => 5
                ]]),
                "location" => 4,
                "sales_channel" => "App",
                "date" => Carbon::today(),

            ],
            [
                'Authorization' => "Bearer $this->token"
            ]
        );

        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("This code is not valid. (For you) ()", $data["message"]);
    }

    public function test_Response_500_without_location()
    {

        $this->logIn();
        $this->location = Location::find(4);

        $this->masterCategory = factory(Category::class)->create();
        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $this->masterCategory->id
        ]);
        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id' => $this->service->id,
            'prices' => 2000
        ]);

        $this->voucher = factory(Voucher::class)->create([]);
//POST WITHOUT LOCATION
        $response = $this->post("v3/customers/1/orders/promotions/add",
            [
                "services" => json_encode([[
                    "id" => $this->service->id,
                    "option" => [0],
                    "quantity" => 5
                ]]),
//                "location" => 4,
                "sales_channel" => "App",
                "date" => Carbon::today(),
                "code" => $this->voucher->code

            ],
            [
                'Authorization' => "Bearer $this->token"
            ]
        );

        $data = $response->decodeResponseJson();
        $this->assertEquals(500, $data['code']);
        $this->assertEquals("Something went wrong.", $data["message"]);
    }

    public function test_Response_403_Invalid_sales_channel()
    {

        $this->logIn();
        $this->location = Location::find(4);

        $this->masterCategory = factory(Category::class)->create();
        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $this->masterCategory->id
        ]);
        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id
        ]);

        factory(CategoryLocation::class)->create([
            'category_id' => $this->masterCategory->id,
            'location_id' => $this->location->id
        ]);
        factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        LocationService::create([
            'location_id' => $this->location->id,
            'service_id' => $this->service->id,
            'prices' => 2000
        ]);

        $this->voucher = factory(Voucher::class)->create([
            'rules' => json_encode(['sales_channels' => ['web']])
        ]);
//POST WITHOUT different channel
        $response = $this->post("v3/customers/1/orders/promotions/add",
            [
                "services" => json_encode([[
                    "id" => $this->service->id,
                    "option" => [0],
                    "quantity" => 5
                ]]),
                "location" => 4,
                "sales_channel" => "App",
                "date" => Carbon::today(),
                "code" => $this->voucher->code

            ],
            [
                'Authorization' => "Bearer $this->token"
            ]
        );

        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("This code is not valid. (For selected channel)", $data["message"]);
    }

}
