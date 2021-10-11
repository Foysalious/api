<?php namespace Tests\Feature\sProOrderCreate;

use App\Models\CustomerDeliveryAddress;
use App\Models\Location;
use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\PartnerWalletSetting;
use App\Models\PartnerWorkingHour;
use App\Models\Profile;
use App\Models\ResourceSchedule;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\CategoryPartner\CategoryPartner;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\PartnerService\PartnerService;
use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;
use Tests\Feature\FeatureTestCase;

class sProOrderCreatePlaceOrderTest extends FeatureTestCase
{

    private $today;
    private $master_category;
    private $secondaryCategory;
    private $service;
    private $deliveryAddress;

    public function setUp()
    {

        parent::setUp();

        DB::table('salesman')->truncate();

        $this->truncateTables([
            Category::class, Service::class, CategoryPartner::class, PartnerResource::class, ResourceSchedule::class,
            LocationService::class, PartnerService::class, CustomerDeliveryAddress::class, CategoryLocation::class, PartnerWorkingHour::class, PartnerWalletSetting::class
        ]);

        $this->logIn();

        $this->location = Location:: find(4);

        $this->deliveryAddress = factory(CustomerDeliveryAddress::class)->create([
            'customer_id' => $this->customer->id,
            'location_id' => $this->location->id
        ]);

        $this->partner -> update([
            'geo_informations' => '{"lat":"23.788099544655","lng":"90.412001016086","radius":"500"}'
        ]);

        $this->master_category = factory(Category::class)->create();

        $this->secondaryCategory = factory(Category::class)->create([
            'name' => 'Car Wash',
            'bn_name' => 'গাড়ী ধোয়া',
            'parent_id' => $this->master_category->id,
            'publication_status' => 1
        ]);

        $this->secondary_category_location_id= factory(CategoryLocation::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'location_id' => $this->location->id
        ]);

        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'variable_type' => ServiceType::FIXED,
            'variables' => '{"price":"1700","min_price":"1000","max_price":"2500","description":""}',
            'publication_status' => 1,
            'stock_left' => 100
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

        $this->master_category = factory(ResourceSchedule::class)->create([
            'resource_id' => $this->resource->id,
        ]);

        $this->partner_resource ->update([
            'resource_type' => 'Handyman'
        ]);

        $this->location_service= factory(LocationService::class)->create(
            [
                'service_id' => $this->service->id,
                'location_id' => $this->location->id
            ]);

        DB::insert('insert into salesman(partner_id) values (?)', [$this->partner->id]);

        DB::insert('insert into partner_service(partner_id, service_id, is_verified, is_published) values (?, ?, ?, ?)', [$this->partner->id, $this->service->id, 1, 1]);

        DB::insert('insert into partner_wallet_settings(partner_id, min_withdraw_amount, max_withdraw_amount, security_money, security_money_received, min_wallet_threshold) values (?, ?, ?, ?, ?, ?)', [$this->partner->id, 1000, 10000, 500, 1, 1000]);

        DB::insert('insert into partner_working_hours(partner_id, day, start_time, end_time) values (?, ?, ?, ?)', [$this->partner->id, 'Friday', '09:00:00', '18:00:00']);


    }

    public function testSProPlaceOrderAPIWithValidBody()
    {
        //arrange
        $services = json_encode([
            ['id' => 1, 'option' => [], 'quantity' => 1]
        ]);

        //act
        $response = $this->post('/v2/resources/orders', [
            'services' => $services,
            'name' => "Kazi Fahd Zakwan",
            'mobile' => "01835559988",
            'sales_channel' => "Web",
            'payment_method' => "cod",
            'date' => '2021-10-22',
            'time' => '15:00:00-16:00:00',
            'location_id' => 4,
            'address' => 'Michael road',
            'partner' => $this->partner->id
        ], [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        dd($data);

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals($this->today, $data["schedule"]["date"]);
        $this->assertEquals(16, $data["schedule"]["slot"]["id"]);
    }

}
