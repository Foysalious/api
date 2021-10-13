<?php namespace Tests\Feature\sProOrderCreate;

use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Job;
use App\Models\Location;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\PartnerResource;
use App\Models\PartnerWalletSetting;
use App\Models\PartnerWorkingHour;
use App\Models\Profile;
use App\Models\Promotion;
use App\Models\ResourceSchedule;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\CategoryPartner\CategoryPartner;
use Sheba\Dal\JobService\JobService;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\PartnerService\PartnerService;
use Sheba\Dal\Service\Service;
use Tests\Feature\FeatureTestCase;

class sProOrderCreatePlaceOrderTest extends FeatureTestCase
{

    private $today;
    private $master_category;
    private $secondaryCategory;
    private $service;

    public function setUp()
    {

        parent::setUp();

        DB::table('salesman')->truncate();

        DB::table('location_partner_service')->truncate();

        $this->truncateTables([
            Category::class, CategoryLocation::class, Service::class, LocationService::class,
            CustomerDeliveryAddress::class, Customer::class, Profile::class,
            PartnerOrder::class, JobService::class, Voucher::class, Promotion::class, Partner::class,
            CategoryPartner::class, PartnerResource::class, ResourceSchedule::class,
            PartnerService::class,  PartnerWorkingHour::class, PartnerWalletSetting::class, Job::class, Order::class
        ]);

        $this->logIn();

        $this->location = Location::find(4);

        $this->profile -> update([
            'mobile' => '+8801835559988',
            'name' => 'Kazi Fahd Zakwan'
        ]);

        $this->master_category = factory(Category::class)->create();

        $this->master_category_location_id = factory(CategoryLocation::class)->create([
            'category_id' => $this->master_category->id,
            'location_id' => $this->location->id
        ]);

        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $this->master_category->id
        ]);

        $this->secondary_category_location_id = factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id
        ]);

        $this->categoryPartner = factory(CategoryPartner::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'partner_id' => $this->partner->id,
            'min_order_amount' => 0.00,
            'is_home_delivery_applied' => 1,
            'is_partner_premise_applied' => 0,
            'uses_sheba_logistic' => 0,
            'delivery_charge' => 0.00,
            'preparation_time_minutes' => 0
        ]);

        $this->partner_resource ->update([
            'resource_type' => 'Handyman'
        ]);

        $this->location_service = factory(LocationService::class)->create([
            'service_id' => $this->service->id,
            'location_id' => $this->location->id
        ]);

        $this->partner -> update([
            'geo_informations' => '{"lat":23.788099544655,"lng":90.412001016086,"radius":"200"}'
        ]);

        DB::insert('insert into salesman(partner_id) values (?)', [$this->partner->id]);

        DB::insert('insert into partner_service(partner_id, service_id, is_verified, is_published) values (?, ?, ?, ?)', [$this->partner->id, $this->service->id, 1, 1]);

        DB::insert('insert into partner_wallet_settings(partner_id, min_withdraw_amount, max_withdraw_amount, security_money, security_money_received, min_wallet_threshold) values (?, ?, ?, ?, ?, ?)', [$this->partner->id, 1000, 10000, 500, 1, 1000]);

        DB::insert('insert into partner_working_hours(partner_id, day, start_time, end_time) values (?, ?, ?, ?)', [$this->partner->id, 'Friday', '09:00:00', '18:00:00']);

        DB::insert('insert into location_partner_service(partner_service_id , location_id) values (?, ?)', [1, 4]);

    }

    public function testSProPlaceOrderAPIWithValidBody()
    {
        //arrange
        $services = json_encode([
            ['id' => $this -> service -> id, 'option' => [0,0], 'quantity' => 1]
        ]);

        //act
        $response = $this->post('/v2/resources/orders', [
            "services" => $services,
            'name' => $this->profile->name,
            'mobile' => $this->profile->mobile,
            'sales_channel' => "App",
            'payment_method' => "cod",
            'date' => '2021-10-22',
            'time' => '15:00:00-16:00:00',
            'location_id' => $this->location->id,
            'address' => 'Michael road',
            'partner' => $this->partner->id
        ], [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();

        $this->job = Job::latest('id')->first()->toArray();
        $this->order = Order::latest('id')->first();
        $code = $this->order->code();
        $this->order->toArray();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
    }

}
