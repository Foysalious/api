<?php namespace Tests\Feature\OrderManagement;

use App\Models\CustomerDeliveryAddress;
use App\Models\Job;
use App\Models\PartnerOrder;
use App\Models\Resource;
use App\Models\ScheduleSlot;
use Sheba\Dal\JobService\JobService;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;
use Tests\Feature\FeatureTestCase;

class NewOrderLIstTest extends FeatureTestCase
{

    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    public $partner_order;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    public $partner_order_request;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    public $job;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    public $customer_delivery_address;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    public $schedule_slot;
    /**
     * @var \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed
     */
    public $job_service;

    public function setUp()
    {
        parent::setUp();
        $this->logIn();
        $this->partner_order = factory(PartnerOrder::class)->create();
        $this->job = factory(Job::class)->create();
        $this->partner_order_request = factory(PartnerOrderRequest::class)->create();
        $this->customer_delivery_address = factory(CustomerDeliveryAddress::class)->create();
        $this->schedule_slot = factory(ScheduleSlot::class)->create();
        $this->job_service = factory(JobService::class)->create();
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
        $response = $this->get("/v1/partners/".$this->partner->id."/order-requests?remember_token=".$this->resource->remember_token."&filter=all");
        $data = $response->decodeResponseJson();
        $this->assertEquals(200,$data['code']);
        $this->assertEquals("Successful",$data['message']);
        $this->assertEquals(0,count($data['orders']));
    }
}