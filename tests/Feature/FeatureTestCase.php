<?php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\Bonus;
use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Department;
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
use App\Models\TopUpVendor;
use App\Sheba\InventoryService\InventoryServerClient;
use App\Sheba\PosOrderService\PosOrderServerClient;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Schema;
use Sheba\Dal\AuthorizationRequest\AuthorizationRequest;
use Sheba\Dal\AuthorizationToken\AuthorizationToken;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Dal\JobService\JobService;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Sheba\OAuth2\AccountServerClient;
use Sheba\Services\Type as ServiceType;
use Tests\Mocks\MockAccountServerClient;
use Tests\Mocks\MockInventoryServerClient;
use Tests\Mocks\MockPosOrderServerClient;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @author Shafiqul Islam <shafiqul@sheba.xyz>
 */
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
    /** @var PartnerBonus */
    protected $PartnerBonus;
    /** @var ParnerSubscriptionPackage $partner_package */
    protected $partner_package;
    /** @var Business $business */
    protected $business;
    /** @var $business_member */
    protected $business_member;
    protected $PosOrder;
    /** @var PosCustomer $PosCustomer */
    protected $PosCustomer;
    /** @var PartnerDeliveryInfoFactory $PartnerDeliveryInfoFactory */
    protected $PartnerDeliveryInfoFactory;
    /** @var PartnerPosService $PartnerPosService */
    protected $PartnerPosService;
    /** @var PartnerPosCategory $PartnerPosCategory */
    protected $PartnerPosCategory;
    /** @var PosCategory $PosCategory */
    protected $PosCategory;
    /** @var PosOrderPayment $PosOrderPayment */
    protected $PosOrderPayment;
    protected $SubscriptionWisePaymentGateways;
    /** @var TopUpVendor $topupVendor */
    protected $topupVendor;
    /** @var Category $secondaryCategory */
    protected $secondaryCategory;
    /** @var JobService $job_service */
    protected $job_service;
    /** @var PartnerOrder $partner_order */
    protected $partner_order;
    /** @var Location $location */
    protected $location;
    /** @var CustomerDeliveryAddress $customer_delivery_address */
    protected $customer_delivery_address;
    /** @var Order $order */
    protected $order;
    /** @var Job $job */
    protected $job;
    protected $businessDepartment;

    public function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(InventoryServerClient::class, MockInventoryServerClient::class);
        $this->app->singleton(PosOrderServerClient::class, MockPosOrderServerClient::class);
        $this->app->singleton(AccountServerClient::class, MockAccountServerClient::class);
    }

    public function get($uri, array $headers = [])
    {
        $uri = trim($this->baseUrl, '/').'/'.trim($uri, '/');

        return parent::get($uri, $headers);
    }

    public function post($uri, array $data = [], array $headers = [])
    {
        $uri = trim($this->baseUrl, '/').'/'.trim($uri, '/');

        return parent::post($uri, $data, $headers);
    }


    public function delete($uri, array $data = [], array $headers = [])
    {
        $uri = trim($this->baseUrl, '/').'/'.trim($uri, '/');

        return parent::delete($uri, $data, $headers);
    }

    public function postWithFiles($uri, array $data = [], array $files = [], array $headers = []): FeatureTestCase
    {
        $server = $this->transformHeadersToServerVars($headers);
        $uri = trim($this->baseUrl, '/').'/'.trim($uri, '/');
        $this->call("POST", $uri, $data, [], $files, $server);

        return $this;
    }

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {
         /*\Illuminate\Support\Facades\DB::unprepared(file_get_contents('database/seeds/sheba_testing.sql'));
         $this->artisan('migrate');
         $this->beforeApplicationDestroyed(function () {
             \Illuminate\Support\Facades\DB::unprepared(file_get_contents('database/seeds/sheba_testing.sql'));
         });*/

        $this->beforeApplicationDestroyed(function () {
            foreach ($this->app->make('db')->getConnections() as $connection) {
                $connection->disconnect();
            }

            RefreshDatabaseState::$migrated = false;
        });
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
            Business::class,
            BusinessMember::class,
            BusinessDepartment::class,
            BusinessRole::class,
        ]);

        $this->profile = Profile::factory()->create();
        $this->createClientAccounts();
    }

    /**
     * @param  array  $tables
     * @return void
     */
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
        $this->affiliate = Affiliate::factory()->for($this->profile)->create();
        $this->customer = Customer::factory()->for($this->profile)->create();
        $this->resource = Resource::factory()->for($this->profile)->create();
        $this->partner_package = PartnerSubscriptionPackage::factory()->create();
        $this->partner = Partner::factory()->create([
            'package_id'         => $this->partner_package->id,
            'subscription_rules' => '{"resource_cap":{"value":5,"is_published":1},"commission":{"value":20,"is_published":1},"fee":{"monthly":{"value":95,"is_published":1},"yearly":{"value":310,"is_published":1},"half_yearly":{"value":410,"is_published":0}},"access_rules":{"loan":true,"dashboard_analytics":true,"pos":{"invoice":{"print":true,"download":true},"due":{"alert":true,"ledger":true},"inventory":{"warranty":{"add":true}},"report":false,"ecom":{"product_publish":false,"webstore_publish":true}},"extra_earning":{"topup":true,"movie":true,"transport":true,"utility":true},"resource":{"type":{"add":true}},"expense":true,"extra_earning_global":true,"customer_list":true,"marketing_promo":true,"digital_collection":true,"old_dashboard":false,"notification":true,"eshop":true,"emi":true,"due_tracker":true},"tags":{"monthly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"half_yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"\u09ae\u09be\u09b8\u09bf\u0995","price":95,"duration":30,"is_published":0},{"title":"yearly","title_bn":"\u09ac\u09be\u09ce\u09b8\u09b0\u09bf\u0995","price":310,"duration":365,"is_published":0},{"title":"two_yearly","title_bn":"\u09a6\u09cd\u09ac\u09bf-\u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":735,"duration":730,"is_published":1},{"title":"3_monthly","title_bn":"\u09e9 \u09ae\u09be\u09b8","price":285,"duration":90,"is_published":0},{"title":"6_monthly","title_bn":"\u09ec \u09ae\u09be\u09b8","price":570,"duration":180,"is_published":0},{"title":"9_monthly","title_bn":"\u09ef \u09ae\u09be\u09b8","price":855,"duration":270,"is_published":0},{"title":"11_month","title_bn":"egaro mash","price":880,"duration":330,"is_published":1},{"title":"13_month","title_bn":"month","price":900,"duration":800,"is_published":1}]}',
            'billing_type'       => "monthly",
        ]);
        $this->member = Member::factory()->for($this->profile)->create();
        $this->business = Business::factory()->create();
        $this->partner_resource = PartnerResource::factory()->create(
            ['resource_id' => $this->resource->id, 'partner_id' => $this->partner->id, 'resource_type' => "Admin"]);
        Department::factory()->create();
        $this->businessDepartment = BusinessDepartment::factory()->create([
            'business_id' => $this->business->id
        ]);
        BusinessRole::factory()->create([
            'business_department_id' => 1
        ]);
        $this->business_member = BusinessMember::factory()->create(
            ['business_id' => $this->business->id,
                'member_id' => $this->member->id,
                'department' => 1,
                'business_role_id' => 1]);
    }

    protected function generateToken(): string
    {
        $data = [
            'name'                     => $this->profile->name,
            'image'                    => $this->profile->pro_pic,
            'profile'                  => [
                'id'             => $this->profile->id,
                'name'           => $this->profile->name,
                'email_verified' => $this->profile->email_verified,
            ],
            'customer'                 => [
                'id' => $this->customer->id,
            ],
            'resource'                 => [
                'id'      => $this->resource->id,
                "partner" => [
                    "id"         => $this->partner->id,
                    "name"       => $this->partner->name,
                    "sub_domain" => $this->partner->sub_domain,
                    "logo"       => $this->partner->logo,
                    "is_manager" => true,
                ],
            ],
            'member'                   => [
                'id' => $this->member->id,
            ],
            'business_member'          => [
                'id'          => $this->business_member->id,
                'business_id' => $this->business->id,
                'member_id'   => $this->member->id,
                'is_super'    => 1,
            ],
            'affiliate'                => [
                'id' => $this->affiliate->id,
            ],
            'logistic_user'            => null,
            'bank_user'                => null,
            'strategic_partner_member' => null,
            'avatar'                   => null,
            "exp"                      => Carbon::now()->addDay()->timestamp,
        ];

        return JWTAuth::customClaims($data)->fromUser($this->profile);
    }

    private function createAuthTables()
    {
        $authorization_request = AuthorizationRequest::factory()->for($this->profile)->create();
        AuthorizationToken::factory()->create(
            ['authorization_request_id' => $authorization_request->id, 'token' => $this->token]);
    }

    protected function mxOrderCreate()
    {
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

        $this->secondaryCategory = Category::factory()->create(
            ['parent_id' => $master_category->id, 'publication_status' => 1]);
        $this->secondaryCategory->locations()->attach($this->location->id);
        $service = Service::factory()->create([
            'category_id'        => $this->secondaryCategory->id,
            'variable_type'      => ServiceType::FIXED,
            'variables'          => '{"price":"1700","min_price":"1000","max_price":"2500","description":""}',
            'publication_status' => 1,
        ]);
        $this->customer_delivery_address = CustomerDeliveryAddress::factory()->create(
            ['customer_id' => $this->customer->id]);
        $this->order = Order::factory()->create([
            'customer_id'      => $this->customer->id,
            'partner_id'       => $this->partner->id,
            'delivery_address' => $this->customer_delivery_address->address,
            'location_id'      => $this->location->id,
        ]);

        $this->partner_order = PartnerOrder::factory()->create([
            'partner_id' => $this->partner->id,
            'order_id'   => $this->order->id,

        ]);

        $this->job = Job::factory()->create([
            'partner_order_id'      => $this->partner_order->id,
            'category_id'           => $this->secondaryCategory->id,
            'service_id'            => $service->id,
            'service_variable_type' => $service->variable_type,
            'service_variables'     => $service->variables,
            'resource_id'           => $this->resource->id,
            'schedule_date'         => "2021-02-16",
            'preferred_time'        => "19:48:04-20:48:04",
            'preferred_time_start'  => "19:48:04",
            'preferred_time_end'    => "20:48:04",
        ]);

        $this->job_service = JobService::factory()->create([
            'job_id'        => $this->job->id,
            'service_id'    => $this->job->service_id,
            'name'          => $this->job->service_name,
            'variable_type' => $this->job->service_variable_type,
            'variables'     => json_encode([]),
        ]);
    }

    /**
     * @param $mobile
     * @param $email
     * @return void
     */
    protected function logInWithMobileNEmail($mobile, $email = null)
    {
        $this->createAccountWithMobileNEmail($mobile, $email);
        $this->token = $this->generateToken();
        $this->createAuthTables();
    }

    /**
     * @param $mobile
     * @param $email
     * @return void
     */
    private function createAccountWithMobileNEmail($mobile, $email = null)
    {
        $this->profile = Profile::factory()->create(['mobile' => $mobile, 'email' => $email,]);

        $this->createClientAccounts();
    }

    protected function truncateTable($table)
    {
        $this->truncateTables([$table]);
    }
}
