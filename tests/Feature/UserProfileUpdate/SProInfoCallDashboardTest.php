<?php namespace Tests\Feature\UserProfileUpdate;

use App\Models\CustomerDeliveryAddress;
use App\Models\Job;
use App\Models\Location;
use App\Models\Order;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\InfoCall\InfoCall;
use Sheba\Dal\InfoCallRejectReason\InfoCallRejectReason;
use Sheba\Dal\InfoCallStatusLogs\InfoCallStatusLog;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\ResourceTransaction\Model;
use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;
use Tests\Feature\FeatureTestCase;

class SProInfoCallDashboardTest extends FeatureTestCase
{
    private $infocall;
    private $year;
    private $month;
    private $infocall_Reject_reason;
    private $infocall_Status_log;
    private $today;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTable(InfoCall::class);

        $this->truncateTable(InfoCallRejectReason::class);

        $this->truncateTable(InfoCallStatusLog::class);

        $this->truncateTable(Model::class);

        $this->logIn();
    }

    public function testInfoCallDashboardAPIForServiceRequest()
    {
        //arrange
        $year = Carbon::now()->year;

        $month = Carbon::now()->month;

        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app'
        ]);

        //act
        $response = $this->get('/v2/resources/info-call/dashboard?year=' . $year . '&month='. $month ,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_rewards"]);

    }

    public function testInfoCallDashboardAPIForCancelledOrder()
    {
        //arrange
        $year = Carbon::now()->year;

        $month = Carbon::now()->month;

        $this->infoCall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
            'status' => "Rejected"
        ]);

        $this->infocall_Reject_reason = factory(InfoCallRejectReason::class)->create();

        //act
        $response = $this->get('/v2/resources/info-call/dashboard?year=' . $year . '&month='. $month ,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_rewards"]);

    }

    public function testInfoCallDashboardAPIForTotalOrder()
    {
        //arrange
        $year = Carbon::now()->year;

        $month = Carbon::now()->month;

        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
            'status' => "Converted"
        ]);

        $this->infocall_Reject_reason = factory(InfoCallRejectReason::class)->create();

        $this->infocall_Status_log = factory(InfoCallStatusLog::class)->create();

        $this->location = Location::find(1);

        $this->truncateTables([
            Category::class,
            Service::class,
            CategoryLocation::class,
            LocationService::class,
            CustomerDeliveryAddress::class,
            Order::class,
            PartnerOrder::class,
            Job::class
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
            'location_id'=>$this->location->id,
            'info_call_id' => 1
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

        $this->resource_Transaction = factory(Model::class)->create([
            'job_id' => $this->job->id,
        ]);

        //act
        $response = $this->get('/v2/resources/info-call/dashboard?year=' . $year . '&month='. $month ,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_rewards"]);

    }

    public function testInfoCallDashboardAPIForCompletedOrder()
    {
        //arrange
        $year = Carbon::now()->year;

        $month = Carbon::now()->month;

        $today = Carbon::now()->toDateTimeString();

        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
            'status' => "Converted"
        ]);

        $this->infocall_Reject_reason = factory(InfoCallRejectReason::class)->create();

        $this->infocall_Status_log = factory(InfoCallStatusLog::class)->create();

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
            'location_id'=>$this->location->id,
            'info_call_id' => 1
        ]);

        $this->partner_order = factory(PartnerOrder::class)->create([
            'partner_id'=>$this->partner->id,
            'order_id'=>$this->order->id,
            'closed_and_paid_at'=>$today
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

        $this->resource_Transaction = factory(Model::class)->create([
            'job_id' => $this->job->id,
            'created_at' => $today
        ]);

        //act
        $response = $this->get('/v2/resources/info-call/dashboard?year=' . $year . '&month='. $month ,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(1000, $data["service_request_dashboard"]["total_rewards"]);

    }

    public function testInfoCallDashboardAPIWithoutMonthAndYearParameter()
    {
        //arrange
        $today = Carbon::now()->toDateTimeString();

        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
        ]);


        //act
        $response = $this->get('/v2/resources/info-call/dashboard',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_rewards"]);

    }

    public function testInfoCallDashboardAPIWithInvalidMonth13()
    {
        //arrange
        $year = Carbon::now()->year;

        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
        ]);

        //act
        $response = $this->get('/v2/resources/info-call/dashboard?year='. $year . '&month=13',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The month must be between 1 and 12.', $data["message"]);
    }

    public function testInfoCallDashboardAPIWithInvalidMonth0()
    {
        //arrange
        $year = Carbon::now()->year;

        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
        ]);

        //act
        $response = $this->get('/v2/resources/info-call/dashboard?year='. $year . '&month=0',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The month must be between 1 and 12.', $data["message"]);
    }

    public function testInfoCallDashboardAPIWithInvalidMonthString()
    {
        //arrange
        $year = Carbon::now()->year;

        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
        ]);

        //act
        $response = $this->get('/v2/resources/info-call/dashboard?year='. $year . '&month=abc',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The month must be an integer.', $data["message"]);
    }

    public function testInfoCallDashboardAPIWithInvalidYearString()
    {
        //arrange
        $month = Carbon::now()->month;

        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
        ]);

        //act
        $response = $this->get('/v2/resources/info-call/dashboard?year=abc&month=' . $month ,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The year must be an integer.', $data["message"]);
    }

    public function testInfoCallDashboardAPIWithoutMonthAndYearValue()
    {
        //arrange
        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
        ]);

        //act
        $response = $this->get('/v2/resources/info-call/dashboard?year=&month=',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The month field is required.The year field is required.', $data["message"]);
    }

    public function testInfoCallDashboardAPIWithOnlyMonthParameter()
    {
        //arrange
        $month = Carbon::now()->month;

        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
        ]);

        //act
        $response = $this->get('/v2/resources/info-call/dashboard?month=' . $month,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_rewards"]);
    }

    public function testInfoCallDashboardAPIWithOnlyYearParameter()
    {
        //arrange
        $year = Carbon::now()->year;

        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
        ]);

        //act
        $response = $this->get('/v2/resources/info-call/dashboard?year=' . $year,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_rewards"]);
    }
}
