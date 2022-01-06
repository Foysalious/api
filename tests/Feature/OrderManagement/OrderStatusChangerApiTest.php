<?php

namespace Tests\Feature\OrderManagement;

use App\Models\Job;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\PartnerResource;
use App\Models\Profile;
use App\Models\Resource;
use App\Models\ResourceSchedule;
use App\Repositories\ResourceJobRepository;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\CategoryPartner\CategoryPartner;
use Sheba\Dal\JobUpdateLog\JobUpdateLog;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockSuccessfulResourceJobRepository;

/**
 * @author Mahanaz Tabassum <mahanaz.tabassum@sheba.xyz>
 */
class OrderStatusChangerApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->logIn();
        $this->mxOrderCreate();
        $this->job_one = Job::find(1);
        $this->job_one->update(["resource_id" => null]);
        $this->categoryPartner = CategoryPartner::factory()->create([
            'partner_id'  => $this->partner->id,
            'category_id' => $this->secondaryCategory->id,
        ]);
        DB::table('category_resource')->truncate();
        DB::table('category_partner_resource')->truncate();
        DB::table('partner_order_requests')->truncate();
        $this->secondaryCategory->partnerResources()->attach($this->partner_resource);
        $this->secondaryCategory->resources()->attach($this->resource);
        $this->truncateTables([
            ResourceSchedule::class,
            JobUpdateLog::class,
        ]);
    }

    /**
     * test cases for accept order post api
     */
    public function testResourceCannotWorkForTheJobIfPartnerAndResourceDontMatch()
    {
        $profile_two = Profile::factory()->create([
            'name'                           => 'testUser2',
            'mobile'                         => '+8801741741741',
            'email'                          => 'hopkins@sheba.xyz',
            'password'                       => bcrypt('89890'),
            'is_blacklisted'                 => 0,
            'mobile_verified'                => 1,
            'email_verified'                 => 1,
            'nid_verification_request_count' => 0,
            'blood_group'                    => 'AB+',
        ]);
        $resource_two = Resource::factory()->create([
            'profile_id' => $profile_two->id,
        ]);

        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $resource_two->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Resource doesn't work for you", $data['message']);
    }

    public function testAdminResourceTypeCannotAcceptOrder()
    {
        $handyman_resource_delete = PartnerResource::find(2);
        $handyman_resource_delete->delete();
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Resource doesn't work for you", $data['message']);
    }

    public function testOrderAcceptedSuccessfully()
    {
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }

    public function testResourceIsAssignedOnAcceptedOrder()
    {
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $job = Job::find($this->job->id);
        $job_log = JobUpdateLog::where('job_id', $this->job->id)->first();
        $log = '{"msg":"Resource Change","old_resource_id":null,"new_resource_id":'.$this->resource->id.'}';
        $this->assertEquals($this->resource->id, $job->resource_id);
        $this->assertEquals($log, $job_log->log);
    }

    public function testAlreadyAcceptedOrderCannotBeAccepted()
    {
        $job = Job::find(1);
        $job->update(['status' => 'Accepted']);

        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();

        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Accepted job cannot be accepted.", $data['message']);
    }

    public function testFirstAvailableResourceIsAssignedInAcceptedOrder()
    {
        $profile_two = Profile::factory()->create([
            'name'                           => 'testUser2',
            'mobile'                         => '+8801741741741',
            'email'                          => 'hopkins@sheba.xyz',
            'password'                       => bcrypt('89890'),
            'is_blacklisted'                 => 0,
            'mobile_verified'                => 1,
            'email_verified'                 => 1,
            'nid_verification_request_count' => 0,
            'blood_group'                    => 'AB+',
        ]);
        $resource_two = Resource::factory()->create([
            'profile_id' => $profile_two->id,
        ]);
        $partner_resource_two = PartnerResource::factory()->create([
            'resource_id'   => $resource_two->id,
            'partner_id'    => $this->partner->id,
            'resource_type' => 'Handyman',
        ]);
        $order_two = Order::factory()->create([
            'customer_id'      => $this->customer->id,
            'partner_id'       => $this->partner->id,
            'delivery_address' => $this->customer_delivery_address->address,
            'location_id'      => $this->location->id,
        ]);

        $partner_order_two = PartnerOrder::factory()->create([
            'partner_id' => $this->partner->id,
            'order_id'   => $order_two->id,

        ]);

        $job_two = Job::factory()->create([
            'partner_order_id'      => $partner_order_two->id,
            'category_id'           => $this->secondaryCategory->id,
            'service_id'            => $this->service->id,
            'service_variable_type' => $this->service->variable_type,
            'service_variables'     => $this->service->variables,
            'resource_id'           => null,
            'schedule_date'         => "2021-02-16",
            'preferred_time'        => "19:48:04-20:48:04",
            'preferred_time_start'  => "19:48:04",
            'preferred_time_end'    => "20:48:04",
        ]);
        $this->job_one->update(["resource_id" => $this->resource->id]);
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$job_two->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $resource_two->id,
        ]);
        $data = $response->decodeResponseJson();
        $job_two_resource = Job::find(2);
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals($resource_two->id, $job_two_resource->resource_id);
    }

    public function testProcessedOrderCannotBeAccepted()
    {
        $job = Job::find(1);
        $job->update(['status' => 'Process']);

        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Process job cannot be accepted.", $data['message']);
    }

    public function testServeDueOrderCannotBeAccepted()
    {
        $job = Job::find(1);
        $job->update(['status' => 'Serve Due']);

        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Serve Due job cannot be accepted.", $data['message']);
    }

    public function testServedOrderCannotBeAccepted()
    {
        $job = Job::find(1);
        $job->update(['status' => 'Served']);

        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Served job cannot be accepted.", $data['message']);
    }

    public function testScheduleDueOrderCannotBeAccepted()
    {
        $job = Job::find(1);
        $job->update(['status' => 'Schedule Due']);

        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Schedule Due job cannot be accepted.", $data['message']);
    }

    public function testDeclinedOrderCannotBeAccepted()
    {
        $job = Job::find(1);
        $job->update(['status' => 'Declined']);

        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Declined job cannot be accepted.", $data['message']);
    }

    public function testCancelledOrderCannotBeAccepted()
    {
        $job = Job::find(1);
        $job->update(['status' => 'Cancelled']);

        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Cancelled job cannot be accepted.", $data['message']);
    }

    public function testNotRespondedOrderCannotBeAccepted()
    {
        $job = Job::find(1);
        $job->update(['status' => 'Not Responded']);

        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept', [
            'remember_token' => $this->resource->remember_token,
            'resource_id'    => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }

    /**
     * partner_order -> id update to null
     * create partner_order_request with partner_id & partner_order_id
     * request status update to missed
     * hit api to sync partner_order_request can be accepted
     */
    public function testMissedPartnerOrderRequestCannotBeAccepted()
    {
        $partner_order_request = factory(PartnerOrderRequest::class)->create([
            'partner_id'       => $this->partner->id,
            'partner_order_id' => $this->partner_order->id,
        ]);
        $partner_order_request->update(['status' => 'missed']);
        $response = $this->post(
            'v1/partners/'.$this->partner->id.'/order-requests/'.$partner_order_request->id.'/accept',
            [
                'remember_token' => $this->resource->remember_token,
            ]
        );
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("missed is not acceptable.", $data['message']);
    }

    public function testAcceptedPartnerOrderRequestCannotBeAccepted()
    {
        $partner_order_request = factory(PartnerOrderRequest::class)->create([
            'partner_id'       => $this->partner->id,
            'partner_order_id' => $this->partner_order->id,
        ]);
        $partner_order_request->update(['status' => 'Accepted']);
        $response = $this->post(
            'v1/partners/'.$this->partner->id.'/order-requests/'.$partner_order_request->id.'/accept',
            [
                'remember_token' => $this->resource->remember_token,
            ]
        );
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
    }

    public function testAssignedOrderCannotBeChangedByUnassignedPartner()
    {
        $partner_order_request = PartnerOrderRequest::factory()->create([
            'partner_id'       => $this->partner->id,
            'partner_order_id' => $this->partner_order->id,
        ]);
        $partner_one = Partner::factory()->create([
            'id' => 2,
        ]);
        $response = $this->post(
            'v1/partners/'.$partner_one->id.'/order-requests/'.$partner_order_request->id.'/accept',
            [
                'remember_token' => $this->resource->remember_token,
            ]
        );
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Forbidden. You're not a manager of this partner.", $data['message']);
    }

    public function testDeclinedOrderStatus()
    {
        /**
         * hit job api with reject action :  https://api.dev-sheba.xyz/v1/partners/37732/jobs/202549/reject
         */
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response = $this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/reject', [
            'remember_token' => $this->resource->remember_token,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }
}
