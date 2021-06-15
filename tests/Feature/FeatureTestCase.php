<?php namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Job;
use App\Models\Location;
use App\Models\Member;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\PartnerResource;
use App\Models\PartnerSubscriptionPackage;
use App\Models\Profile;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Sheba\Dal\AuthorizationRequest\AuthorizationRequest;
use Sheba\Dal\AuthorizationToken\AuthorizationToken;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\JobService\JobService;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Sheba\Services\Type as ServiceType;
use Sheba\Subscription\Partner\PartnerPackage;
use TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class FeatureTestCase extends TestCase
{
    use DatabaseMigrations;

    protected $token;
    /** @var Profile */
    protected $profile;
    /** @var Affiliate */
    protected $affiliate;
    /** @var Customer */
    protected $customer;
    /** @var Resource */
    protected $resource;
    /** @var Member */
    protected $member;
    /** @var Partner */
    protected $partner;
    /** @var PartnerResource */
    protected $partner_resource;
    // @var ParnerSubscriptionPackage
    protected $partner_package;
    /** @var $partner_order */
    protected $partner_order;
    /** @var $partner_order_request */
    protected $partner_order_request;
    /** @var $job */
    protected $job;
    /** @var $customer_delivery_address */
    protected $customer_delivery_address;
    /** @var $schedule_slot */
    protected $schedule_slot;
    /** @var $job_service */
    protected $job_service;
    /** @var $order */
    protected $order;
    /** @var $location */
    protected $location;
    /** @var $service */
    protected $service;
    /** @var $service */
    protected $secondaryCategory;
     /** @var $business */
    protected $business;
    /** @var $business_member */
    private $business_member;

    public function setUp()
    {
        parent::setUp();
    }

    public function get($uri, array $headers = [])
    {
        $uri = trim($this->baseUrl, '/') . '/' . trim($uri, '/');
        return parent::get($uri, $headers);
    }

    public function post($uri, array $data = [], array $headers = [])
    {
        $uri = trim($this->baseUrl, '/') . '/' . trim($uri, '/');
        return parent::post($uri, $data, $headers);
    }

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {
         /* NO NEED TO RUN

        \Illuminate\Support\Facades\DB::unprepared(file_get_contents('database/seeds/sheba_testing.sql'));*/
         //$this->artisan('migrate');
      /*   * $this->beforeApplicationDestroyed(function () {
         * \Illuminate\Support\Facades\DB::unprepared(file_get_contents('database/seeds/sheba_testing.sql'));
         * });*/
    }

    protected function logIn()
    {

        $this->createAccounts();
        $this->token = $this->generateToken();
        $this->createAuthTables();
    }

    private function createAccounts()
    {
        $this->truncateTables([
            Profile::class,
            Affiliate::class,
            Customer::class,
            Member::class,
            Resource::class,
            Partner::class,
            PartnerResource::class,
            Business::class,
            BusinessMember::class
        ]);


        $this->profile = factory(Profile::class)->create();



        $this->createClientAccounts();
    }

    protected function truncateTables(array $tables)
    {
        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            $table::truncate();
        }
        Schema::enableForeignKeyConstraints();
    }

    private function createClientAccounts()
    {

        $this->affiliate = factory(Affiliate::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->customer = factory(Customer::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->resource = factory(Resource::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->partner_package = factory(PartnerSubscriptionPackage::class)->create();
        $this->partner = factory(Partner::class)->create([
            'package_id' => $this->partner_package->id
        ]);
        $this->partner_resource = factory(PartnerResource::class)->create([
            'resource_id' => $this->resource->id,
            'partner_id' => $this->partner->id,
            'resource_type'=>'Admin'
        ]);
        $this->partner_resource = factory(PartnerResource::class)->create([
            'resource_id' => $this->resource->id,
            'partner_id' => $this->partner->id,
            'resource_type'=>'Handyman'
        ]);
        $this->member = factory(Member::class)->create([
            'profile_id' => $this->profile->id
        ]);
        $this->business = factory(Business::class)->create();
        $this->business_member = factory(BusinessMember::class)->create([
            'business_id' => $this->business->id, 'member_id' => $this->member->id
        ]);
    }

    protected function generateToken()
    {
        return JWTAuth::fromUser($this->profile, [
            'name' => $this->profile->name, 'image' => $this->profile->pro_pic, 'profile' => [
                'id' => $this->profile->id, 'name' => $this->profile->name, 'email_verified' => $this->profile->email_verified
            ], 'customer' => [
                'id' => $this->customer->id

            ],
            'resource' => [
                'id'=>$this->resource->id
            ],
            'member' => [
                'id' => $this->member->id
            ], 'business_member' => [
                'id' => $this->business_member->id, 'business_id' => $this->business->id, 'member_id' => $this->member->id, 'is_super' => 1
            ], 'affiliate' => [
                'id' => $this->affiliate->id
            ], 'logistic_user' => null, 'bank_user' => null, 'strategic_partner_member' => null, 'avatar' => null, "exp" => Carbon::now()->addDay()->timestamp
        ]);
    }

    private function createAuthTables()
    {
        $authorization_request = factory(AuthorizationRequest::class)->create([
            'profile_id' => $this->profile->id
        ]);
        factory(AuthorizationToken::class)->create([
            'authorization_request_id' => $authorization_request->id, 'token' => $this->token
        ]);
    }
    protected function mxOrderCreate(){
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

    protected function logInWithMobileNEmail($mobile, $email = null)
    {
        $this->createAccountWithMobileNEmail($mobile, $email);
        $this->token = $this->generateToken();
        $this->createAuthTables();

    }

    private function createAccountWithMobileNEmail($mobile, $email = null)
    {
        $this->profile = factory(Profile::class)->create([

            'mobile' => $mobile, 'email' => $email,

        ]);


        $this->createClientAccounts();
    }

    protected function truncateTable($table)
    {
        $this->truncateTables([
            $table
        ]);
    }
}
