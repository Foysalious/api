<?php namespace Tests\Feature\OrderManagement;

use App\Models\CustomerDeliveryAddress;
use App\Models\Job;
use App\Models\Location;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\Resource;
use App\Models\ScheduleSlot;
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
            'resource_id'=>$this->resource->id
        ]);
        $this->job_service = factory(JobService::class)->create([
            'job_id'=>$this->job->id,
            'service_id'=>$this->job->service_id,
            'name'=>$this->job->service_name,
            'variable_type'=>$this->job->service_variable_type,
            'variables'=>json_encode([])
        ]);
    }

    public function testWithoutFilterResponseCode()
    {
        $response=$this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The filter field is required.", $data['message']);
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
    public function testCustomerNameAvalailableOnResponse()
    {

        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertEquals($this->profile->name,$data['orders'][0]['customer_name']);
        $this->assertEquals("Successful",$data['message']);
        $this->assertEquals(0,count($data['orders']));
    }

}