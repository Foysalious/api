<?php namespace Tests\Feature\OrderManagement;


use App\Jobs\Job;
use App\Models\Location;
use App\Models\Partner;
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
        $this->job1 = \App\Models\Job::find(1);
        $this->job1->update(["resource_id" => null]);
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
           /* $profile_three = factory(Profile::class)->create([
            'name' => 'testUser3', /* faker generates a name
            'mobile' =>'+8801742742742',
            'email' =>'hawkins@sheba.xyz',
            'password' =>bcrypt('09098'),
            'is_blacklisted'=> 0,
            'mobile_verified'=>1,
            'email_verified'=>1,
            'nid_verification_request_count'=>0,
            'blood_group'=>'AB+'
        ]);*/
            $resource_two = factory(Resource::class)->create([
            'profile_id' => $profile_two->id
        ]);
           /* $resource_three = factory(Resource::class)->create([
            'profile_id' => $profile_three->id
        ]);*/
           /* $partner_two = factory(Partner::class)->create([
            'name'=> 'testSP1',
            'package_id'=>2,
            'mobile' => '+8801741741741',
            'password' => bcrypt('89890'),
            'status' => 'Verified',
            'wallet' => 50000
        ]);
            $partner_three = factory(Partner::class)->create([
            'name'=> 'testSP2',
            'package_id'=>2,
            'mobile' => '+8801742742742',
            'password' => bcrypt('09098'),
            'status' => 'Verified',
            'wallet' => 50000
        ]);*/
            /*$partner_resource_two = factory(PartnerResource::class)->create([
            'resource_id' => $resource_two->id,
            'partner_id' => $this->partner->id,
            'resource_type'=>'Admin'
        ]);*/
        //dd($this->partner_resource_two);
            /*$partner_resource_three = factory(PartnerResource::class)->create([
            'resource_id' => $resource_three->id,
            'partner_id' => $this->partner->id,
            'resource_type'=>'Admin'
        ]);*/
        //dd($this->resource->remember_token);
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
        //dd($response);
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
}
