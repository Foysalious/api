<?php namespace Tests\Feature\OrderManagement;


use App\Jobs\Job;
use App\Models\Location;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\PartnerResource;
use App\Models\Profile;
use App\Models\Resource;
use App\Models\ResourceSchedule;
use App\Repositories\ResourceJobRepository;
use Carbon\Carbon;
use Factory\JobFactory;
use Factory\ResourceScheduleFactory;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Object_;
use Sheba\Dal\CategoryPartner\CategoryPartner;
use Sheba\Dal\JobUpdateLog\JobUpdateLog;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockSuccessfulResourceJobRepository;

class AcceptNewOrderTest extends FeatureTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->logIn();
        $this->mxOrderCreate();
        $this->job_one = \App\Models\Job::find(1);
        $this->job_one->update(["resource_id" => null]);
        $this->categoryPartner = factory(CategoryPartner::class)->create([
            'partner_id'=>$this->partner->id,
            'category_id'=>$this->secondaryCategory->id
        ]);
        DB::table('category_resource')->truncate();
        DB::table('category_partner_resource')->truncate();
        $this->secondaryCategory->partnerResources()->attach($this->partner_resource);
        $this->secondaryCategory->resources()->attach($this->resource);
        $this->truncateTables([
            ResourceSchedule::class,
            JobUpdateLog::class
        ]);

    }
    /* test cases for accept order post api */
    public function testResourceCannotWorkForTheJobIfPartnerAndResourceDontMatch(){
            $profile_two = factory(Profile::class)->create([
            'name' => 'testUser2', /* faker generates a name */
            'mobile' =>'+8801741741741',
            'email' =>'hopkins@sheba.xyz',
            'password' =>bcrypt('89890'),
            'is_blacklisted'=> 0,
            'mobile_verified'=>1,
            'email_verified'=>1,
            'nid_verification_request_count'=>0,
            'blood_group'=>'AB+'
        ]);
            $resource_two = factory(Resource::class)->create([
            'profile_id' => $profile_two->id
        ]);

        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $resource_two->id
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Resource doesn't work for you", $data['message']);
    }

    public function testAdminResourceTypeCannotAcceptOrder()
    {
        $handyman_resource_delete = PartnerResource::find(2);
        $handyman_resource_delete->delete();
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $this->resource->id,
        ]);
        //dd($response);
        $data = $response->decodeResponseJson();

        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Resource doesn't work for you", $data['message']);
    }

    public function testOrderAcceptedSuccessfully()
    {
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $this->resource->id,
        ]);
        dd($response);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }

    public function testResourceIsAssignedOnAcceptedOrder()
    {
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $this->resource->id,
        ]);
        //dd($response);
        $data = $response->decodeResponseJson();
        $job = \App\Models\Job::find($this->job->id);
        $job_log = JobUpdateLog::where('job_id',$this->job->id)->first();
        //dd($job_log);
        $log = '{"msg":"Resource Change","old_resource_id":null,"new_resource_id":'.$this->resource->id.'}';
        $this->assertEquals($this->resource->id,$job->resource_id);
        $this->assertEquals($log,$job_log->log);
    }

    public function testAlreadyAcceptedOrderCannotBeAccepted()
    {
        //arrange
        $job = \App\Models\Job::find(1);
        $job -> update(['status' => 'Accepted']);

        //act
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Accepted job cannot be accepted.", $data['message']);
    }

    public function testFirstAvailableResourceIsAssignedInAcceptedOrder()
    {
        $profile_two = factory(Profile::class)->create([
            'name' => 'testUser2',
            'mobile' =>'+8801741741741',
            'email' =>'hopkins@sheba.xyz',
            'password' =>bcrypt('89890'),
            'is_blacklisted'=> 0,
            'mobile_verified'=>1,
            'email_verified'=>1,
            'nid_verification_request_count'=>0,
            'blood_group'=>'AB+'
        ]);
        $resource_two = factory(Resource::class)->create([
            'profile_id'=> $profile_two->id
        ]);
        $partner_resource_two = factory(PartnerResource::class)->create([
            'resource_id' => $resource_two->id,
            'partner_id' => $this->partner->id,
            'resource_type'=>'Handyman'
        ]);
        $order_two = factory(Order::class)->create([
            'customer_id'=>$this->customer->id,
            'partner_id'=>$this->partner->id,
            'delivery_address'=>$this->customer_delivery_address->address,
            'location_id'=>$this->location->id
        ]);

        $partner_order_two = factory(PartnerOrder::class)->create([
            'partner_id'=>$this->partner->id,
            'order_id'=>$order_two->id

        ]);

        $job_two = factory(\App\Models\Job::class)->create([
            'partner_order_id'=>$partner_order_two->id,
            'category_id'=>$this->secondaryCategory->id,
            'service_id'=>$this->service->id,
            'service_variable_type'=>$this->service->variable_type,
            'service_variables'=>$this->service->variables,
            'resource_id'=>null,
            'schedule_date'=>"2021-02-16",
            'preferred_time'=>"19:48:04-20:48:04",
            'preferred_time_start'=>"19:48:04",
            'preferred_time_end'=>"20:48:04"
        ]);
        $this->job_one->update(["resource_id" => $this->resource->id]);
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$job_two->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $resource_two->id
        ]);
        $data = $response->decodeResponseJson();
        $job_two_resource = \App\Models\Job::find(2);
        //dd($job_two_resource);
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
        $this->assertEquals($resource_two->id,$job_two_resource->resource_id);
    }

    public function testProcessedOrderCannotBeAccepted()
    {
        //arrange
        $job = \App\Models\Job::find(1);
        $job -> update(['status' => 'Process']);

        //act
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Process job cannot be accepted.", $data['message']);
    }

    public function testServeDueOrderCannotBeAccepted()
    {
        //arrange
        $job = \App\Models\Job::find(1);
        $job -> update(['status' => 'Serve Due']);

        //act
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Serve Due job cannot be accepted.", $data['message']);
    }

    public function testServedOrderCannotBeAccepted()
    {
        //arrange
        $job = \App\Models\Job::find(1);
        $job -> update(['status' => 'Served']);

        //act
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Served job cannot be accepted.", $data['message']);
    }

    public function testScheduleDueOrderCannotBeAccepted()
    {
        //arrange
        $job = \App\Models\Job::find(1);
        $job -> update(['status' => 'Schedule Due']);

        //act
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Schedule Due job cannot be accepted.", $data['message']);
    }

    public function testDeclinedOrderCannotBeAccepted()
    {
        //arrange
        $job = \App\Models\Job::find(1);
        $job -> update(['status' => 'Declined']);

        //act
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Declined job cannot be accepted.", $data['message']);
    }

    public function testCancelledOrderCannotBeAccepted()
    {
        //arrange
        $job = \App\Models\Job::find(1);
        $job -> update(['status' => 'Cancelled']);

        //act
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("Cancelled job cannot be accepted.", $data['message']);
    }

    public function testNotRespondedOrderCannotBeAccepted()
    {
        //arrange
        $job = \App\Models\Job::find(1);
        $job -> update(['status' => 'Not Responded']);

        //act
        $this->app->singleton(ResourceJobRepository::class, MockSuccessfulResourceJobRepository::class);
        $response=$this->post('v1/partners/'.$this->partner->id.'/jobs/'.$this->job->id.'/accept',[
            'remember_token' => $this->resource->remember_token,
            'resource_id' => $this->resource->id,
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }
}
