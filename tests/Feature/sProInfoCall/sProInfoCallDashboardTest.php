<?php

namespace Tests\Feature\sProInfoCall;

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
use Throwable;

/**
 * @author Dolon Banik <dolon@sheba.xyz>
 */
class SProInfoCallDashboardTest extends FeatureTestCase
{
    private $infocall;
    private $infocall_Reject_reason;
    private $infocall_Status_log;

    public function setUp(): void
    {
        parent::setUp();

        $this->truncateTable(InfoCall::class);

        $this->truncateTable(InfoCallRejectReason::class);

        $this->truncateTable(InfoCallStatusLog::class);

        $this->truncateTable(Model::class);

        $this->logIn();
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIForServiceRequest()
    {
        $year = Carbon::now()->year;

        $month = Carbon::now()->month;

        $this->infocall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
        ]);

        $response = $this->get('/v2/resources/info-call/dashboard?year='.$year.'&month='.$month, [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_rewards"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIForCancelledOrder()
    {
        $year = Carbon::now()->year;

        $month = Carbon::now()->month;

        $this->infoCall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
            'status'          => "Rejected",
        ]);

        $this->infocall_Reject_reason = InfoCallRejectReason::factory()->create();

        $response = $this->get('/v2/resources/info-call/dashboard?year='.$year.'&month='.$month, [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_rewards"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIForTotalOrder()
    {
        $year = Carbon::now()->year;

        $month = Carbon::now()->month;

        $this->infocall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
            'status'          => "Converted",
        ]);

        $this->infocall_Reject_reason = InfoCallRejectReason::factory()->create();

        $this->infocall_Status_log = InfoCallStatusLog::factory()->create();

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

        $master_category = Category::factory()->create();

        $this->secondaryCategory = Category::factory()->create([
            'parent_id'          => $master_category->id,
            'publication_status' => 1,
        ]);

        $this->secondaryCategory->locations()->attach($this->location->id);

        $this->service = Service::factory()->create([
            'category_id'        => $this->secondaryCategory->id,
            'variable_type'      => ServiceType::FIXED,
            'variables'          => '{"price":"1700","min_price":"1000","max_price":"2500","description":""}',
            'publication_status' => 1,
        ]);

        $this->customer_delivery_address = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $this->order = Order::factory()->create([
            'customer_id'      => $this->customer->id,
            'partner_id'       => $this->partner->id,
            'delivery_address' => $this->customer_delivery_address->address,
            'location_id'      => $this->location->id,
            'info_call_id'     => 1,
        ]);

        $this->partner_order = PartnerOrder::factory()->create([
            'partner_id' => $this->partner->id,
            'order_id'   => $this->order->id,
        ]);

        $this->job = Job::factory()->create([
            'partner_order_id'      => $this->partner_order->id,
            'category_id'           => $this->secondaryCategory->id,
            'service_id'            => $this->service->id,
            'service_variable_type' => $this->service->variable_type,
            'service_variables'     => $this->service->variables,
            'resource_id'           => $this->resource->id,
            'schedule_date'         => "2021-02-16",
            'preferred_time'        => "19:48:04-20:48:04",
            'preferred_time_start'  => "19:48:04",
            'preferred_time_end'    => "20:48:04",
        ]);

//        $this->resource_Transaction = Model::factory()->create([
//            'job_id' => $this->job->id,
//        ]);

        $response = $this->get('/v2/resources/info-call/dashboard?year='.$year.'&month='.$month, [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_rewards"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIForCompletedOrder()
    {
        $year = Carbon::now()->year;

        $month = Carbon::now()->month;

        $today = Carbon::now()->toDateTimeString();

        $this->infocall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
            'status'          => "Converted",
        ]);

        $this->infocall_Reject_reason = InfoCallRejectReason::factory()->create();

        $this->infocall_Status_log = InfoCallStatusLog::factory()->create();

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

        $master_category = Category::factory()->create();

        $this->secondaryCategory = Category::factory()->create([
            'parent_id'          => $master_category->id,
            'publication_status' => 1,
        ]);

        $this->secondaryCategory->locations()->attach($this->location->id);

        $this->service = Service::factory()->create([
            'category_id'        => $this->secondaryCategory->id,
            'variable_type'      => ServiceType::FIXED,
            'variables'          => '{"price":"1700","min_price":"1000","max_price":"2500","description":""}',
            'publication_status' => 1,
        ]);

        $this->customer_delivery_address = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $this->order = Order::factory()->create([
            'customer_id'      => $this->customer->id,
            'partner_id'       => $this->partner->id,
            'delivery_address' => $this->customer_delivery_address->address,
            'location_id'      => $this->location->id,
            'info_call_id'     => 1,
        ]);

        $this->partner_order = PartnerOrder::factory()->create([
            'partner_id'         => $this->partner->id,
            'order_id'           => $this->order->id,
            'closed_and_paid_at' => $today,
        ]);

        $this->job = Job::factory()->create([
            'partner_order_id'      => $this->partner_order->id,
            'category_id'           => $this->secondaryCategory->id,
            'service_id'            => $this->service->id,
            'service_variable_type' => $this->service->variable_type,
            'service_variables'     => $this->service->variables,
            'resource_id'           => $this->resource->id,
            'schedule_date'         => "2021-02-16",
            'preferred_time'        => "19:48:04-20:48:04",
            'preferred_time_start'  => "19:48:04",
            'preferred_time_end'    => "20:48:04",
        ]);

//        $this->resource_Transaction = Model::factory()->create([
//            'job_id'     => $this->job->id,
//            'created_at' => $today,
//        ]);

        $response = $this->get('/v2/resources/info-call/dashboard?year='.$year.'&month='.$month, [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["completed_order"]);
//        $this->assertEquals(1000, $data["service_request_dashboard"]["total_rewards"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIWithoutMonthAndYearParameter()
    {
        $today = Carbon::now()->toDateTimeString();

        $this->infocall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
        ]);

        $response = $this->get('/v2/resources/info-call/dashboard', [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_rewards"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIWithInvalidMonth13()
    {
        $year = Carbon::now()->year;

        $this->infocall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
        ]);

        $response = $this->get('/v2/resources/info-call/dashboard?year='.$year.'&month=13', [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The month must be between 1 and 12.', $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIWithInvalidMonth0()
    {
        $year = Carbon::now()->year;

        $this->infocall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
        ]);

        $response = $this->get('/v2/resources/info-call/dashboard?year='.$year.'&month=0', [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The month must be between 1 and 12.', $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIWithInvalidMonthString()
    {
        $year = Carbon::now()->year;

        $this->infocall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
        ]);

        $response = $this->get('/v2/resources/info-call/dashboard?year='.$year.'&month=abc', [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The month must be an integer.', $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIWithInvalidYearString()
    {
        $month = Carbon::now()->month;

        $this->infocall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
        ]);

        $response = $this->get('/v2/resources/info-call/dashboard?year=abc&month='.$month, [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The year must be an integer.', $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIWithoutMonthAndYearValue()
    {
        $this->infocall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
        ]);

        $response = $this->get('/v2/resources/info-call/dashboard?year=&month=', [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals('The month field is required.The year field is required.', $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIWithOnlyMonthParameter()
    {
        $month = Carbon::now()->month;

        $this->infocall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
        ]);

        $response = $this->get('/v2/resources/info-call/dashboard?month='.$month, [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);
        $this->assertEquals(1, $data["service_request_dashboard"]["total_service_requests"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["service_requests"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["cancelled_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["completed_order"]);
        $this->assertEquals(0, $data["service_request_dashboard"]["total_rewards"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallDashboardAPIWithOnlyYearParameter()
    {
        $year = Carbon::now()->year;

        $this->infocall = InfoCall::factory()->create([
            'created_by'      => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name'     => 'resource-app',
        ]);

        $response = $this->get('/v2/resources/info-call/dashboard?year='.$year, [
            'Authorization' => "Bearer $this->token",
        ]);

        $data = $response->decodeResponseJson();

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
