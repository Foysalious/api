<?php namespace Tests\Feature\UserProfileUpdate;

use App\Models\CustomerDeliveryAddress;
use App\Models\Job;
use App\Models\Location;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\InfoCall\InfoCall;
use Sheba\Dal\InfoCallRejectReason\InfoCallRejectReason;
use Sheba\Dal\InfoCallStatusLogs\InfoCallStatusLog;
use Sheba\Dal\JobService\JobService;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;
use Tests\Feature\FeatureTestCase;
use App\Models\Order;
use Sheba\Dal\ResourceTransaction\Model;

class SProInfoCallDetailsTest extends FeatureTestCase
{
    private $infocall;
    private $infocall_id;
    private $infocall_Reject_reason;
    private $infocall_Status_log;
    private $resource_Transaction;
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

    public function testInfoCallDetailsAPIForInvalidInfoCallId()
    {
        //arrange
        $this->infoCall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app'
        ]);

        //act
        $response_main = $this->get('/v2/resources/info-call/123456',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data_main["code"]);
        $this->assertEquals('InfoCall not found.', $data_main["message"]);
    }

    public function testInfoCallDetailsAPIForPassingChacracterAsInfoCallId()
    {
        //arrange
        $this->infoCall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app'
        ]);

        //act
        $response_main = $this->get('/v2/resources/info-call/abcdef',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(404, $data_main["code"]);
        $this->assertEquals('InfoCall not found.', $data_main["message"]);
    }

    public function testInfoCallDetailsAPIForPassingInvalidURL()
    {
        //arrange
        $this->infoCall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app'
        ]);

        $response = $this->get('/v2/resources/info-call',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        $infocall_id = $data["service_request_list"][0]["service_request_id"];

        //act
        $response_main = $this->get('/v2/resources/info-callss'. $infocall_id,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals("404 Not Found", $data_main["message"]);
    }

    public function testInfoCallDetailsAPIForInvalidAuthorizedToken()
    {
        //arrange
        $this->infoCall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app'
        ]);

        //act
        $response = $this->get('/v2/resources/info-call',
            [
                'Authorization' => "Bearer ttttttttttttttt"
            ]);

        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(401, $data["code"]);
        $this->assertEquals('Your session has expired. Try Login', $data["message"]);
    }

    public function testInfoCallDetailsAPIForOpenInfoCall()
    {
        //arrange
        $this->infocall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app'
        ]);

        $response = $this->get('/v2/resources/info-call',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        $infocall_id = $data["service_request_list"][0]["service_request_id"];

        //act
        $response_main = $this->get('/v2/resources/info-call/'. $infocall_id,
            [
                'Authorization' => "Bearer $this->token"
            ]);
        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals($infocall_id, $data_main["info_call_details"]["id"]);
        $this->assertEquals($this->infocall->status, $data_main["info_call_details"]["info_call_status"]);
        $this->assertEquals($this->infocall->service_name, $data_main["info_call_details"]["service_name"]);

    }

    public function testInfoCallDetailsAPIForRejectedInfoCall()
    {
        //arrange
        $this->infoCall = factory(InfoCall::class)->create([
            'created_by' => $this->resource->id,
            'created_by_type' => get_class($this->resource),
            'portal_name' => 'resource-app',
            'status' => "Rejected"
        ]);

        $this->infocall_Reject_reason = factory(InfoCallRejectReason::class)->create();

        $this->infocall_Status_log = factory(InfoCallStatusLog::class)->create();

        $response = $this->get('/v2/resources/info-call',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        $infocall_id = $data["service_request_list"][0]["service_request_id"];

        //act
        $response_main = $this->get('/v2/resources/info-call/'. $infocall_id,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals("Successful", $data_main["message"]);
        $this->assertEquals($infocall_id, $data_main["info_call_details"]["id"]);
        $this->assertEquals("Rejected", $data_main["info_call_details"]["info_call_status"]);
        $this->assertEquals("বাতিল হয়েছে", $data_main["info_call_details"]["bn_info_call_status"]);
        $this->assertEquals("Customer Unreachable", $data_main["info_call_details"]["service_comment"]);
        $this->assertEquals("Ac service", $data_main["info_call_details"]["service_name"]);
    }

    public function testInfoCallDetailsAPIForConvertedInfoCallRunning()
    {
        //arrange
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

        $response = $this->get('/v2/resources/info-call',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        $infocall_id = $data["service_request_list"][0]["service_request_id"];

        //act
        $response_main = $this->get('/v2/resources/info-call/'. $infocall_id,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals("Successful", $data_main["message"]);
        $this->assertEquals($infocall_id, $data_main["info_call_details"]["id"]);
        $this->assertEquals("Converted", $data_main["info_call_details"]["info_call_status"]);
        $this->assertEquals("গ্রহণ হয়েছে", $data_main["info_call_details"]["bn_info_call_status"]);
        $this->assertEquals($this->order->id, $data_main["info_call_details"]["order_id"]);
        $this->assertEquals("Running", $data_main["info_call_details"]["order_status"]);
        $this->assertEquals("চলছে", $data_main["info_call_details"]["bn_order_status"]);
        $this->assertEquals("Ac service", $data_main["info_call_details"]["service_name"]);

    }

    public function testInfoCallDetailsAPIForConvertedInfoCallCompleted()
    {
        //arrange
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
        ]);

        $response = $this->get('/v2/resources/info-call',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        $infocall_id = $data["service_request_list"][0]["service_request_id"];

        //act
        $response_main = $this->get('/v2/resources/info-call/'. $infocall_id,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals("Successful", $data_main["message"]);
        $this->assertEquals($infocall_id, $data_main["info_call_details"]["id"]);
        $this->assertEquals("Converted", $data_main["info_call_details"]["info_call_status"]);
        $this->assertEquals("গ্রহণ হয়েছে", $data_main["info_call_details"]["bn_info_call_status"]);
        $this->assertEquals($this->order->id, $data_main["info_call_details"]["order_id"]);
        $this->assertEquals("Completed", $data_main["info_call_details"]["order_status"]);
        $this->assertEquals("শেষ", $data_main["info_call_details"]["bn_order_status"]);
        $this->assertEquals("1000", $data_main["info_call_details"]["reward"]);
        $this->assertEquals("Ac service", $data_main["info_call_details"]["service_name"]);
    }

    public function testInfoCallDetailsAPIForConvertedInfoCallCanceled()
    {
        //arrange
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
            'cancelled_at'=>$today
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


        $response = $this->get('/v2/resources/info-call',
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data = $response->decodeResponseJson();

        $infocall_id = $data["service_request_list"][0]["service_request_id"];

        //act
        $response_main = $this->get('/v2/resources/info-call/'. $infocall_id,
            [
                'Authorization' => "Bearer $this->token"
            ]);

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals("Successful", $data_main["message"]);
        $this->assertEquals($infocall_id, $data_main["info_call_details"]["id"]);
        $this->assertEquals("Converted", $data_main["info_call_details"]["info_call_status"]);
        $this->assertEquals("গ্রহণ হয়েছে", $data_main["info_call_details"]["bn_info_call_status"]);
        $this->assertEquals($this->order->id, $data_main["info_call_details"]["order_id"]);
        $this->assertEquals("Cancelled", $data_main["info_call_details"]["order_status"]);
        $this->assertEquals("বাতিল", $data_main["info_call_details"]["bn_order_status"]);
        $this->assertEquals("Ac service", $data_main["info_call_details"]["service_name"]);
    }
}
