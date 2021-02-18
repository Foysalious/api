<?php namespace Tests\Feature\OrderManagement;

use App\Models\CustomerDeliveryAddress;
use App\Models\Job;
use App\Models\Location;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\Resource;
use App\Models\ScheduleSlot;
use Carbon\Carbon;
use Sheba\CmDashboard\jobsComplainForCm;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\JobService\JobService;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;
use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;
use Tests\Feature\FeatureTestCase;

class NewOrderLIstTest extends FeatureTestCase
{

    /**
     * @var $partner_order
     */
    private $partner_order;
    /**
     * @var $partner_order_request
     */
    private $partner_order_request;
    /**
     * @var $job
     */
    private $job;
    /**
     * @var $customer_delivery_address
     */
    private $customer_delivery_address;
    /**
     * @var $schedule_slot
     */
    private $schedule_slot;
    /**
     * @var $job_service
     */
    private $job_service;
    /**
     * @var $order
     */
    private $order;
    /**
     * @var $location
     */
    private $location;
    /**
     * @var $service
     */
    private $service;
    /**
     * @var $service
     */
    private $secondaryCategory;


    public function setUp()
    {
        parent::setUp();
        $this->logIn();
        $this->location = Location::find(1);
        $this->truncateTables([
            Category::class,
            Service::class,
            CategoryLocation::class,
            LocationService::class,
            CustomerDeliveryAddress::class,
            Order::class,
            PartnerOrder::class,
            Job::class,

        ]);
        $master_category = factory(Category::class)->create();

        $this->secondaryCategory = factory(Category::class)->create([
            'parent_id' => $master_category->id,
            'publication_status' => 1
        ]);
        $this->secondaryCategory->locations()->attach($this->location->id);
        $this->service = factory(Service::class)->create([
            'category_id' => $this->secondaryCategory->id,
            'variable_type' => ServiceType::FIXED,
            'variables' => '{"price":"1700","min_price":"1000","max_price":"2500","description":""}',
            'publication_status' => 1
        ]);
        $this->customer_delivery_address = factory(CustomerDeliveryAddress::class)->create([
            'customer_id'=>$this->customer->id
        ]);
        $this->order = factory(Order::class)->create([
            'customer_id'=>$this->customer->id,
            'partner_id'=>$this->partner->id,
            'delivery_address'=>$this->customer_delivery_address->address,
            'location_id'=>$this->location->id
        ]);

        $this->partner_order = factory(PartnerOrder::class)->create([
            'partner_id'=>$this->partner->id,
            'order_id'=>$this->order->id

        ]);

        $this->job = factory(Job::class)->create([
            'partner_order_id'=>$this->partner_order->id,
            'category_id'=>$this->secondaryCategory->id,
            'service_id'=>$this->service->id,
            'service_variable_type'=>$this->service->variable_type,
            'service_variables'=>$this->service->variables,
            'resource_id'=>$this->resource->id,
            'schedule_date'=>"2021-02-16",
            'preferred_time'=>"19:48:04-20:48:04",
            'preferred_time_start'=>"19:48:04",
            'preferred_time_end'=>"20:48:04"
        ]);
        $this->job_service = factory(JobService::class)->create([
            'job_id'=>$this->job->id,
            'service_id'=>$this->job->service_id,
            'name'=>$this->job->service_name,
            'variable_type'=>$this->job->service_variable_type,
            'variables'=>json_encode([])
        ]);
    }

    /*
        Test Cases from here
    */
    public function testOrderRequestsWithoutFilterResponseCode()
    {
        $response=$this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The filter field is required.", $data['message']);
    }
    public function testOrderRequestsWithInvalidFilterResponseCode()
    {
        $response=$this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=invalid");
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The selected filter is invalid.", $data['message']);
    }
    public function testOrderRequestsWithInvalidRememberTokenResponseCode()
    {
        $response=$this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=i3zuho3Tw1TxaRC7LIB5Py8KaT9mRtxffv1H1lqZu13rTNUHCXrhxD2h2Nor&limit=200&offset=0&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertEquals(404, $data['code']);
        $this->assertEquals("Partner or Resource not found.", $data['message']);
    }
    public function testEmptyOrderListResponseCode()
    {
        $this->truncateTables([
            Category::class,
            Service::class,
            CategoryLocation::class,
            LocationService::class,
            CustomerDeliveryAddress::class,
            Order::class,
            PartnerOrder::class,
            Job::class,

        ]);
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertEquals(200,$data['code']);
        $this->assertEquals("Successful",$data['message']);
        $this->assertEquals(0,count($data['orders']));
    }
    public function testCustomerNameAvailableOnResponse()
    {

        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertEquals($this->profile->name,$data['orders'][0]['customer_name']);
        $this->assertEquals("Successful",$data['message']);
        $this->assertEquals(0,count($data['orders']));
    }
    /*
    New order request data:
            created_at_readable
            category_mame
            address
            schedule_at
            total_price
    Order Details data:
            code
            schedule_time_start
            schedule_time_end
    */
    // test if api responded with created at readable time key
    public function testCreatedAtReadableTimeKeyPresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey("created_at_readable",$data['orders'][0]);
    }

    // test if api responded with order service category key
    public function testCategoryNameKeyPresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey("category_name",$data['orders'][0]);
    }

    // test if api responded with customer address key
    public function testAddressKeyPresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey("address",$data['orders'][0]);
    }

    // test if api responded with schedule_at key
    public function testScheduleAtKeyPresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey("schedule_at",$data['orders'][0]);
    }

    // test if api responded with total price key
    public function testTotalPriceKeyPresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey("total_price",$data['orders'][0]);
    }

    // test if api responded with order code key
    public function testOrderCodeKeyPresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey("code",$data['orders'][0]);
    }

    // test if api responded with schedule start time key
    public function testScheduleTimeStartKeyPresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey("schedule_time_start",$data['orders'][0]);
    }

    // test if api responded with schedule end time key
    public function testScheduleTimeEndKeyPresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertArrayHasKey("schedule_time_end",$data['orders'][0]);
    }

    //test if api responded with readable time value
    public function testCreatedAtReadableTimeValuePresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertEquals("1 second ago",$data['orders'][0]['created_at_readable']);
    }

    // test if api responded with order service category value
    public function testCategoryNameValuePresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals($this->secondaryCategory->name,$data['orders'][0]['category_name']);
    }

    // test if api responded with customer address value
    public function testAddressValuePresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals("Road#10, Avenue#9, House#1222&1223 Mirpur DOHS, Dhaka.",$data['orders'][0]['address']);
    }

    // test if api responded with schedule_at value
    public function testScheduleAtValuePresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $time = $data['orders'][0]['schedule_at'] ;
        $formatted_date = Carbon::createFromTimestamp($time)->toDateString();
        //dd($formatted_date);
        $this->assertEquals($this->job->schedule_date,$formatted_date);
    }

    // test if api responded with total price value
    public function testTotalPriceValuePresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $price = $this->job_service->quantity*$this->job_service->unit_price;
        $this->assertEquals($this->order->total_price,$price);
    }

    // test if api responded with order code value
    public function testOrderCodeValuePresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertEquals("D-008001-0001",$data['orders'][0]['code']);
    }

    // test if api responded with schedule start time value
    public function testScheduleTimeStartValuePresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertEquals($this->job->preferred_time_start,$data['orders'][0]['schedule_time_start']);
    }

    // test if api responded with schedule end time value
    public function testScheduleTimeEndValuePresentOnResponse()
    {
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertEquals($this->job->preferred_time_end,$data['orders'][0]['schedule_time_end']);
    }
}
